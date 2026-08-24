---
name: Repo Navigation
description: AI assistant entry point for vetapp (Czech zoo/vet clinic management system)
type: reference
scope: repo
source: build
verified: 2026-08-24
---

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Stack:** Vanilla PHP 8.1+, no framework, no Composer (WEDOS hosting doesn't support it — don't suggest Composer-based dependencies). MySQL/MariaDB via PDO. UI is entirely in Czech (staff refer to every feature by its Czech name — don't search for English UI strings).

**Also in this repo, not covered by the sections below:** see [Related Sub-Applications](#related-sub-applications) for `zootrack/`, and [Additional Resources](#additional-resources) for `LDT/`, `.import/`, and `logic_schema/`.

## Deployment

Every push to `main` triggers `.github/workflows/deploy.yml`, which uploads all files via FTP to the production server (`vetapp.zootabor.eu`). There is no build step. **Pushing to main = live deploy.**

Excluded from FTP upload (see `.github/workflows/deploy.yml`): `.git*`, `.github/`, `.claude/`, `.env`, `*.log`, `database/backups/`, `database/*.sql` (DB dumps), `cache/`, `tmp/`, `.import/`, `logic_schema/`, `docs/`, `knowledge.yaml`, and zootrack dev-only files (`zootrack/create_db.py`, `zootrack/sqlite_to_mariadb.php`, `zootrack/MANUAL.md`). Internal docs (`docs/`, `logic_schema/`, `knowledge.yaml`) stay in git but off the public web.

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

## Common Pitfalls to Avoid

### ❌ DON'T

- **Assume a controller checks authorization just because the pattern is documented above.** Not every controller method calls `hasAccess`/`hasPermission`. Grep the specific method before trusting it — see "Known Issues" below for a concrete historical example.
- **Assume `logic_schema/` (see Additional Resources) is current.** It's a static-analysis snapshot dated 2026-06-26. At least two of its top-severity findings (a missing-authorization bug in `WarehouseController`/`UrineAnalysisController`, and a `ValueError` crash in `WarehouseController::central()`) were already fixed in this codebase within hours of that snapshot being generated — the audit predates its own fix. Treat it as a historical reference and a source of *questions to re-verify*, not as ground truth about the current code.
- **Re-run a migration file expecting it to no-op if already applied.** Migrations in `database/migrations/` are not written to be idempotent (e.g. `013_add_zootrack_edit_to_users.php` does a raw `ALTER TABLE ... ADD COLUMN` with no existence guard — MySQL/MariaDB has no portable `IF NOT EXISTS` for this). The `/migrate?key=...` runner tracks executed migrations in a `migrations` table and skips already-run ones during normal operation, but if that tracking table is ever out of sync with actual schema state (manual DB change, restored backup, failed partial apply), re-running will hard-fail with a duplicate-column/table error instead of skipping cleanly.
- **Grep for `urine` and assume you've found every reference to the urine-analysis section.** The `Section` enum / `User::SECTIONS` / `user_permissions.section` key is `urine`, but `index.php` sets `$_SESSION['current_app'] = 'urineanalysis'` (see the color table above, which already uses `urineanalysis`) — the navbar CSS class and session routing state use a different string than the permission system.
- **Confuse the "animal database" with `zootrack/`.** `AnimalDatabaseController` is the main app's central animal registry across workplaces. `zootrack/` (see Related Sub-Applications) is a *different application* tracking European zoo institutions' CITES/holdings data — unrelated tables, unrelated purpose, just an unfortunate name collision.
- **Suggest adding a Composer dependency.** Hosting is WEDOS (`.htaccess` calls it "VEDOS"), which doesn't support Composer. There is no autoloader — every controller method manually `require_once`s the models it uses; a missing `require_once` is a runtime fatal, not a build error.

### ✅ DO

- Verify the actual authorization checks present in a controller method by reading it, not by pattern-matching on the section it belongs to.
- Check the `migrations` table's recorded state before manually re-running or backfilling a migration.
- When searching for the urine-analysis section, check both `urine` (DB/permissions) and `urineanalysis` (session/CSS).
- Trust inline code comments — this codebase is written to be explained inline; if something is unclear, check the surrounding comments first before assuming it's undocumented.

## Known Issues / Historical Findings

**Warehouse/UrineAnalysis authorization gap — found and fixed same day (2026-06-26).** A prior static-analysis audit (`logic_schema/`, generated 2026-06-26 10:24) flagged that `WarehouseController` (create/update/movement/consumption) and `UrineAnalysisController::updateResult` only called `Auth::requireLogin()` without the per-workplace `hasPermission`/`hasAccess` check that `BiochemistryController` performs correctly — an IDOR (any logged-in user could write to warehouse/urine-analysis data for workplaces they had no permission for). **As of this verification pass, that gap no longer exists in the code**: every write method in both controllers (`createItem`, `updateItem`, `addMovement`, `setConsumption`, `saveInventory`, `createTest`, `updateResult`) now calls the `userCan($workplaceId, $section, $perm)` helper (`app/helpers/functions.php:335`), which was added the same afternoon as the audit (file timestamps: audit 2026-06-26 10:24, `functions.php`/`WarehouseController.php`/`UrineAnalysisController.php` all modified 2026-06-26 15:54–15:56). A related crash (`WarehouseController::central()` throwing `ValueError` on `str_repeat('?,', -1)` for a user with zero accessible workplaces) was fixed in the same pass — the guard is now explicit at `WarehouseController.php:119-131` with an inline comment referencing the crash it avoids. **Caveat:** this was verified against the source in this repo, not against what's actually deployed to production — given deploy-on-push-to-main (see Deployment above), if `main` was behind this fix at any point there may have been a live-production window where the gap was exploitable. Worth a quick production sanity check (try a write against a workplace the logged-in test user has no permission for) if this hasn't been done since 2026-06-26.

**Two dead-code findings from the same audit are now also resolved / non-issues:** `database/migrate.php` (the duplicate migration runner) no longer exists in this repo — only `MigrateController`'s `/migrate?key=...` route remains. The `app/views/animals/list.php.backup`/`.broken`/`.working_backup` files the audit flagged as shipping to production are also no longer present.

**Vaccination reminder system is a planned, in-progress feature, not dead code.** `VaccinationPlan::getNotificationDue`/`markNotificationSent` and the `notification_sent_*` columns exist with no scheduler wired up yet — this is intentional (meant to become a cron job) and the module currently only has test data, not production data. Don't "clean up" this code path as unused.

## User Roles & Primary Workflows

Three user types, each mapped to a subset of sections (per `user_permissions`):

- **Keepers (zookeepers):** warehouse-focused — check inventory tasks, record stock movements/consumption (`WarehouseController`).
- **Vets:** lab-result-focused — enter examination/lab results (`parasitology`, `biochemistry`, `urine` sections).
- **Admins:** oversee the system and manage per-user, per-workplace, per-section permissions; bypass all permission checks automatically (see Permission System above).

**Workplaces ("provozy"):** ZOO Tábor, Babice, Lipence, Deponace — the complete current list, not expected to grow. Confirmed empirically: adding a new workplace requires only a new row in the `workplaces` table; no code change needed — every module picks it up automatically.

## Related Sub-Applications

**`zootrack/`** is a distinct application *area* living inside this repo. As of 2026-08-24 it was **merged into this repo** as ordinary tracked files and is **deployed together with the main app** by the same FTP workflow (`/zootrack/` is a subfolder of the shared docroot). It previously had its own `.git` and its own deploy pipeline as a separate repo (`github.com/zoo-tabor/zootrack`, full history preserved there, last commit `a7370cf`); **that separate repo is retired — commit zootrack changes here and push `main`**, do not push to the old repo (its Action would re-deploy stale files). Its dev-only files (`create_db.py`, `sqlite_to_mariadb.php`, `MANUAL.md`) are kept in git but excluded from the FTP upload; `zootrack/.htaccess` hardens direct access on the server.

It tracks European zoo institutions and their CITES/animal-holdings data (`zootrack_institutions`, `zootrack_*`, `zootrack_geocache` tables) — an entirely different data domain from the main app's `animals`/`examinations` tables, despite the animal-adjacent name. It is **not** wired into the main app's routing/`Router`, models, or `Section`/`user_permissions` system: it has its own `index.php`/`api.php` and reuses only the main app's PHP session (`zootrack/auth_check.php` reads `$_SESSION['user_id']`/`role`/`zootrack_edit`) rather than a separate login — `zt_require_login_page()`/`zt_require_login_api()` both require an authenticated session, so it is **not** an open/unauthenticated endpoint (a prior audit claim to the contrary, from before auth was added, is stale). Known gap: dedicated `email`/`phone` columns exist on `zootrack_institutions` in the live DB but have **no migration** (added out-of-band, so not reproducible from `database/migrations/`) and aren't surfaced in the front-end; contact-tracking fields (`last_contact_date`, `contact_notes`, `animals_from_them`, `animals_at_them`) were added via migration 012 and are wired into `app.html`.

## Terminology Quick Reference

See `docs/GLOSSARY.md` for full UI↔code↔DB mappings. Most critical: the `urine`/`urineanalysis` split above, and `Workplace` (code/DB) = *provoz* (what every Czech-speaking user actually calls it).

## Architecture Quick Reference

See `docs/ARCHITECTURE.md` for non-obvious patterns (the `zootrack/` sub-app relationship, the LDT lab-import pipeline, the migration idempotency gotcha, and more detail on the fixed authorization gap above).

## Additional Resources

- `logic_schema/` — a prior static-analysis audit (2026-06-26) with machine-readable `findings.json`/`routes.json`/`database.json`/`functions.json` and an `index.html` viewer. Useful as a *source of questions*, not as current fact — see Common Pitfalls above.
- `LDT/` — real sample `.ldt` files (German "Labordatenträger" lab-export format) plus `LDT_handling_documentation.md`. This is the actual production import path for `BiochemistryImportController`, distinct from any generic CSV template.
- `.import/` — ad hoc spreadsheet staging (`current_stock.xls`, `lexikon.xlsx`) plus `gen_migration.py`, a script that generated some of the numbered migration files.
- [deepwiki.com/zoo-tabor/vetapp](https://deepwiki.com/zoo-tabor/vetapp) — an auto-generated wiki with manual adjustments on top; a secondary external reference for onboarding.
- `docs/GLOSSARY.md`, `docs/ARCHITECTURE.md`, `docs/BUSINESS_CONTEXT.md` — generated Context Tree docs (see links above).

---

*Context tree built: 2026-08-24*
*Based on interview with: janstich (project owner)*
