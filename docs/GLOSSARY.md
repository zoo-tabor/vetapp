---
name: Glossary
description: UI -> Code -> DB terminology mappings for vetapp
type: reference
scope: repo
source: build
verified: 2026-08-24
---

# Glossary: Terminology Mappings

**Purpose:** Critical mappings between UI language, code classes, and database tables

**When to use this:** You're searching the codebase for a feature and can't find it

---

## UI → Code → Database Mappings

### Urine analysis section
- **What users see:** "Analýza moči" (Czech nav label)
- **Permission/DB code term:** `urine` — used in the `Section` enum, `User::SECTIONS`, and the `user_permissions.section` column
- **Session/routing/CSS term:** `urineanalysis` — set into `$_SESSION['current_app']` by `index.php` (`current_app` assignment block) and used as the navbar CSS class / color-table key
- **Why different:** Historical naming drift between the permission-system key and the routing/theming key — nobody unified them.
- **Where to look:** `index.php` (current_app assignment, ~line 42), `app/models/User.php` (`SECTIONS`), `app/controllers/UrineAnalysisController.php`
- **Example:** Grepping the codebase for `'urine'` finds the permission checks (`hasPermission($userId, $wp, 'urine', 'edit')`) but will *miss* the session/CSS key `urineanalysis` — you need both search terms to find every reference to this section.

### Workplace / "Provoz"
- **What users say:** *provoz* (Czech for site/facility/operation) — e.g. "provoz Babice"
- **Code/DB term:** `Workplace` model, `workplaces` table, `workplace_id` column (present on nearly every business table as the tenant-like isolation boundary)
- **Why different:** Not a translation bug — the code is already in English — but a vocabulary gap: staff and the domain expert always say "provoz," never "workplace," so searching docs/commit messages for "workplace" may miss context that only exists under "provoz."
- **Where to look:** `app/models/Workplace.php`, `workplaces` table, `user_permissions.workplace_id`
- **Confirmed list:** ZOO Tábor, Babice, Lipence, Deponace (complete, stable — see `docs/BUSINESS_CONTEXT.md`)

### "Animal database" vs. `zootrack/`
- **Looks like:** Both deal with "animals," easy to conflate.
- **`AnimalDatabaseController` / `AnimalDatabaseController::central()`:** the main app's central animal registry, aggregated across workplaces (`animals` table).
- **`zootrack/`:** a distinct in-repo application area (own `index.php`/`api.php`, merged into this repo 2026-08-24 — no longer a separate git repo/deploy) tracking *European zoo institutions and their CITES/animal holdings* (institutional data, not the clinic's own animals) — `zootrack_institutions`, `zootrack_*`, `zootrack_geocache` tables.
- **Why different:** Name collision only — the two systems share no tables, no controllers, no models.
- **Where to look:** `app/controllers/AnimalDatabaseController.php` vs. `zootrack/index.php`, `zootrack/api.php`

### `app_name` config default
- **What it says:** `app/config/config.php:8` and `.env.example` both default `APP_NAME` to `'Parazitologická Evidence'` ("Parasitology Records")
- **What it actually is:** A historical artifact from when the app covered only the parasitology module, before growing into the current 7-section system (`animals`, `parasitology`, `biochemistry`, `urine`, `vaccination`, `warehouse`, `lexikon`).
- **Why it matters:** Don't infer that parasitology is still the "primary" or default module from this string — it's leftover naming, not a signal about current architecture.
- **Where to look:** `app/config/config.php:8`, `.env.example`

---

## Search Cheat Sheet

Searching for...? Try grepping for:
- Urine-analysis session/theming code → `urineanalysis` (not just `urine`)
- Urine-analysis permission/DB code → `urine`
- Workplace-related code, when the domain expert says "provoz" → `Workplace` / `workplace_id`
- Institutional/CITES zoo-holdings data (not clinic animal records) → `zootrack` / `institutions`
- Bulk lab-result import (biochemistry/hematology) → `LDT` / `BiochemistryImportController`

---

*Mappings verified: 2026-08-24*
*Source: Domain expert interview + code verification*
