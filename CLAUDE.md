# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Deployment

Every push to `main` triggers `.github/workflows/deploy.yml`, which uploads all files via FTP to the production server (`vetapp.zootabor.eu`). There is no build step. **Pushing to main = live deploy.**

Excluded from FTP upload: `.git*`, `.github/`, `.claude/`, `.env`, `*.log`, `database/backups/`, `cache/`, `tmp/`, `.import/`.

## Database Migrations

Migration files live in `database/migrations/NNN_name.php`. Each file must `return function(PDO $pdo) { ... };`.

To run pending migrations on the server: visit `vetapp.zootabor.eu/migrate?key=<MIGRATE_KEY>` (key is in `.env` on the server). The runner tracks which migrations have been executed in a `migrations` table and skips already-run ones.

**Always run migrations after pushing schema changes** — the code is live before the migration runs.

## Architecture

Vanilla PHP, no framework. Single entry point: `index.php`.

- **Routing**: `index.php` registers all routes directly via `Router::get/post($path, $callback)`. Callbacks `require_once` the relevant controller and call the method. Routes use `:param` segments.
- **Controllers**: `app/controllers/` — one class per feature area, no base class. Each controller method `require_once`s every model it uses at the top of the file.
- **Models**: `app/models/` — extend `app/core/Model.php` (PDO wrapper). Use `$this->query($sql, $params)` for SELECT, `$this->execute($sql, $params)` for writes.
- **Views**: `app/views/` — plain PHP templates. Rendered via `View::render('path/template', $data)`. Layout is set by passing `'layout' => 'main'` in the data array; the layout file is `app/views/layouts/main.php`, which includes `header.php` and `footer.php`.
- **Auth**: `Auth::requireLogin()` / `Auth::isAdmin()` / `Auth::userId()` — reads from `$_SESSION`. Call `Auth::requireLogin()` at the top of every controller method.

## Permission System

Three layers:
1. **`users.role`** — ENUM `('admin', 'user')`. Admins bypass all permission checks automatically (enforced in model methods).
2. **`user_permissions` table** — per-user, per-workplace, per-section grants (`can_view`, `can_edit`).
3. **Section** — ENUM `('animals', 'parasitology', 'biochemistry', 'urine', 'vaccination', 'warehouse', 'lexikon')`.

**Canonical section keys** (defined in `User::SECTIONS`):
| Key | Label |
|---|---|
| `animals` | Seznam zvířat |
| `parasitology` | Parazitologie |
| `biochemistry` | Biochemie a hematologie |
| `urine` | Analýza moči |
| `vaccination` | Vakcinační plán |
| `warehouse` | Sklad |
| `lexikon` | Lexikon (future) |

**Key model methods** — admin bypass is built in, never add it in controllers:
- `User::hasPermission($userId, $workplaceId, $section, 'view'|'edit')` → bool
- `User::getWorkplacePermissions($userId, $section)` → workplaces with access
- `User::getAccessibleSections($userId)` → array of section keys
- `Workplace::hasAccess($userId, $workplaceId, $section)` → bool
- `Workplace::getUserWorkplaces($userId, $section)` → workplaces with access

**Pattern for controller access checks:**
```php
if (!$workplace || !$workplaceModel->hasAccess(Auth::userId(), $workplaceId, 'warehouse')) {
    // deny
}
// Edit-specific check:
$canEdit = Auth::isAdmin() || $userModel->hasPermission(Auth::userId(), $workplaceId, 'warehouse', 'edit');
```

Admins always get full access — `hasAccess()`, `hasPermission()`, `getWorkplacePermissions()`, and `getAccessibleSections()` all short-circuit for `Auth::isAdmin()`.

## Section Color Theming

Each section has a navbar color applied via a CSS class on `<nav class="navbar {section}">`. The active section is stored in `$_SESSION['current_app']` and set automatically in `index.php` based on the request URI.

Colors (used in header, admin UI, and any section-scoped style):
| `current_app` | Primary | Dark |
|---|---|---|
| `parasitology` | `#2c3e50` | `#1a252f` |
| `animals` | `#8e44ad` | `#7d3c98` |
| `biochemistry` | `#c0392b` | `#a93226` |
| `urineanalysis` | `#e67e22` | `#d35400` |
| `vaccination` | `#3498db` | `#2980b9` |
| `warehouse` | `#27ae60` | `#229954` |

Admin pages (e.g. `admin/settings.php`) read `$_SESSION['current_app']` and render dynamic CSS using whichever color matches, so the admin UI reflects the section the user came from.

## Adding a New Section

1. Add the section key to the `section` ENUM in a new migration (alter `user_permissions.section`).
2. Add the entry to `User::SECTIONS` constant.
3. Add the navbar color in `header.php` and the `$__appColors` array in `admin/settings.php`.
4. Add the dropdown link in `header.php` guarded by `$__all('newkey')`.
5. Register routes in `index.php`; create controller/views.
