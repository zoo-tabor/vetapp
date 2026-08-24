# Phase 1: Codebase Discovery

**Analyzed:** 2026-08-24
**Project Root:** C:/Users/janstich/Documents/zootabor/vetapp

---

## Tech Stack

**Language:** PHP, no framework, **no Composer** (no `composer.json`/`vendor/`) — zero external PHP dependencies.
**Database:** MySQL/MariaDB via PDO. Two separate live schemas in practice: the main app DB (`d328675_vetapp` per `.env.example`) and a **second, fully independent app** (`zootrack/`) with its own `.env`/`.git`/DB connection.
**Frontend:** Vanilla JS, no bundler. CDN-loaded Chart.js (graphs) and Leaflet + Nominatim + SpeciesPlus (zootrack map only).
**Repo tooling:** Prior static-analysis pass already exists at `logic_schema/` (generated 2026-06-26) with machine-readable `findings.json`, `routes.json`, `database.json`, `functions.json`, an `index.html` viewer, and `summary.md` — this is a goldmine, not something to re-derive.

---

## Architecture Patterns

**Nested independent sub-application:** `zootrack/` is a second, self-contained app living inside this repo — its own `.git`, `.env`, `.htaccess`, `.github/workflows`, `api.php`, `index.php`, `create_db.py` (SQLite→MariaDB migration script). It is NOT part of the main app's routing/Auth/permission system. Confirm with owner whether it deploys via a separate pipeline or piggybacks on the main FTP deploy.

**LDT lab-import pipeline:** `LDT/` contains real sample `.ldt` files (German "Labordatenträger" format used by veterinary/medical labs to export structured results) plus a detailed handling guide (`LDT/LDT_handling_documentation.md`). `BiochemistryImportController` consumes these for bulk biochemistry/hematology import — this is the actual production import path, distinct from any generic CSV template.

**`.import/` staging directory:** one-off spreadsheet sources (`current_stock.xls`, `lexikon.xlsx`/`lexicon.xlsx`/`lexicon.zip`) plus `gen_migration.py`, a script that apparently *generated* one or more of the numbered migration files rather than them being hand-written. Worth asking whether this script is reused for future bulk imports.

**Duplicate migration runner:** both `app/controllers/MigrateController.php` (the documented `/migrate?key=...` route) and `database/migrate.php` exist with near-identical logic (flagged in `logic_schema/findings.json` DUP-01). Only the controller route is documented in CLAUDE.md — the second entry point is undocumented and unclear which is authoritative.

**current_app session value ≠ Section enum key for one section:** `index.php` sets `$_SESSION['current_app'] = 'urineanalysis'` for the urine-analysis area, but the `Section` enum / `User::SECTIONS` / `user_permissions.section` key is `'urine'`. The CLAUDE.md color table already shows `urineanalysis` as the `current_app` key, but this is easy to miss when grepping for `'urine'` and expecting to also find the navbar/session key.

**Prior audit's top severity findings (unverified by me beyond reading, but concrete file:line pointers already exist):**
- `zootrack/api.php` has no authentication at all and `Access-Control-Allow-Origin: *`, including destructive write actions — publicly writable from the internet.
- Several main-app controllers (Warehouse create/update/movement/consumption, UrineAnalysis `updateResult`) only check `Auth::requireLogin()`, not per-workplace `hasPermission`/`hasAccess` — an IDOR pattern that contradicts the "always check hasAccess" guidance in CLAUDE.md. `BiochemistryController::updateResult` does check correctly, so the pattern is inconsistent, not systemic.
- No CSRF protection anywhere in the app.
- `/migrate?key=...` — the key is logged in cleartext to `php_errors.log` on every request via `Router.php` (lines ~30/44/55) since it logs the full `REQUEST_URI` including query string.

---

## Terminology Traps Discovered

### 1. Section key `urine` (DB/permissions) vs `current_app`/CSS value `urineanalysis`
**DB/permission term:** `urine` (in `Section` enum, `user_permissions.section`, `User::SECTIONS`)
**Session/UI routing term:** `urineanalysis` (`$_SESSION['current_app']`, navbar CSS class, color table key)
**Files:** `index.php` (current_app assignment), `app/models/User.php` (SECTIONS), CLAUDE.md color table.
**Why critical:** Grepping for `urine` in the frontend/session-handling code will miss the actual key used for theming/routing state.

### 2. "Provoz" (workplace) — Czech business term vs code term
**UI/domain term:** *provoz* (Czech for "site/facility/operation" — ZOO Tábor, Babice, Lipence, Deponace)
**Code/DB term:** `Workplace` model, `workplaces` table, `workplace_id` everywhere.
**Why non-obvious:** Not a translation trap in code (code is already English), but critical for interview: confirm the four known "provozy" and whether more will be added, since `workplace_id` is the tenant-like isolation boundary threaded through nearly every permission check.

### 3. `zootrack` — same repo, unrelated data domain
**Looks like:** part of the vet app (animal-adjacent name).
**Actually is:** a separate application tracking *European zoo institutions and their animal holdings* (institutional/CITES data), not the clinic's own animal records. Uses `institutions`, `zootrack_*`, and `geocache` tables — unrelated to `animals`/`examinations` in the main schema. Easy to conflate "animal database" (main app's `AnimalDatabaseController`, central animal registry across workplaces) with "zootrack" (external institution/holdings tracker) — these are two different things both dealing with "animals."

### 4. `app_name` config default still says "Parazitologická Evidence"
`app/config/config.php` and `.env.example` both default `APP_NAME` to "Parazitologická Evidence" (Parasitology Records) — a naming leftover from when the app covered only the parasitology module, before it grew into the current 7-section multi-module system. Not wrong, just a historical artifact worth flagging so nobody assumes parasitology is still the "primary" module.

---

## Entry Points & Key Directories

**Entry Points:**
- `index.php` — main app (routes, ~90 registered).
- `database/migrate.php` — a second, largely duplicate migration runner (see Architecture Patterns).
- `zootrack/index.php` and `zootrack/api.php` — fully separate app/API, unauthenticated.

**Key Directories not mentioned in CLAUDE.md:**
- `app/helpers/` — `env.php` (`.env` parsing) and `functions.php` (grab-bag of helpers; several are confirmed dead/broken, see Gotchas).
- `app/config/` — `config.php`, `database.php`, `email.php` (plain PHP arrays returning config, reading via `env()`).
- `LDT/` — real sample lab files + format documentation; primary source for biochemistry bulk import.
- `.import/` — ad hoc import staging (spreadsheets + a migration-generator script).
- `logic_schema/` — a full prior static-analysis report (security/perf/dead-code findings) with machine-readable JSON; treat as a starting point for the interview and for future drift-detection, not something to regenerate blind.
- `zootrack/` — nested sibling application, own git history.

**Controllers not covered by CLAUDE.md's section list:** `DewormingController`, `EnclosureController`, `ExaminationController`, `BiochemistryImportController`, `AnimalDatabaseController`, `AppController`, `PrintController`, `ApiController` — these support cross-cutting or sub-features (examinations, enclosures, deworming, central animal DB, printing, generic search APIs) rather than mapping 1:1 to a `Section` enum key.

---

## Confusing Areas (Interview Seeds)

1. Is `zootrack/` meant to be deployed/maintained as part of this project going forward, or is it legacy/side-project code that happens to live in the same repo? Its total lack of auth on `api.php` is a live, unauthenticated write/delete endpoint on the internet.
2. Which migration runner is authoritative — `MigrateController` (`/migrate?key=`) or `database/migrate.php`? Are they ever both invoked in practice?
3. Why do `WarehouseController` and `UrineAnalysisController::updateResult` skip the `hasPermission`/`hasAccess` checks that `BiochemistryController` performs — intentional trust boundary, or an oversight the owner wants fixed?
4. Is the vaccination reminder system (`VaccinationPlan::getNotificationDue`/`markNotificationSent`, `notification_sent_*` columns) meant to be wired up to a cron job that doesn't exist yet, or is it dead schema?
5. `animals.animal_category` (legacy text column) vs `animal_category_id` (FK) — which is canonical now, and is the text column safe to drop in a future migration?
6. Is `.import/gen_migration.py` a one-off script or a reusable tool for future bulk data imports (e.g., new lexikon updates)?
7. Backup files shipped to production (`app/views/animals/list.php.backup`, `.broken`, `.working_backup`) — safe to delete, or intentionally kept for rollback reference?

---

## Existing Documentation Status

**CLAUDE.md:** Accurate and current for what it covers (deployment, routing, permission system, section theming) — verified against `index.php`, `User.php`, `Workplace.php`. Does not mention `zootrack/`, `LDT/`, `.import/`, `logic_schema/`, or the duplicate migration runner.
**`logic_schema/`:** A thorough, dated (2026-06-26) static-analysis snapshot — security findings, dead code, duplication, performance issues, all with file:line references. Should be treated as a **primary source** for Phase 2 interview questions and possibly folded into the Context Tree directly (e.g., as a security/tech-debt doc) rather than re-discovered.
**`LDT/LDT_handling_documentation.md`:** Detailed, accurate-looking format reference for `.ldt` parsing — useful background for anyone touching `BiochemistryImportController`.
**No README.md found at repo root** (only CLAUDE.md).

---

## Gotchas Identified

- **No Composer, no autoloader** — every controller method manually `require_once`s the models it needs; missing a `require_once` is a runtime fatal, not a build-time error.
- **`functions.php` contains at least two broken/dead helpers**: `hasPermission()` calls an undefined `Auth::canEdit()` (would fatal if ever invoked), and `sanitizeString()` uses `FILTER_SANITIZE_STRING`, removed in PHP 8.1+ — confirms the app targets **PHP 8.1+**, not documented anywhere else.
- **`WarehouseController::central()`** will throw a `ValueError` in PHP 8 (`str_repeat('?,', -1)`) if a user has zero accessible workplaces — `AnimalDatabaseController::central()` guards this case, warehouse does not.
- **Migration secret leakage**: `Router.php` logs the full request URI (including `?key=...`) to `php_errors.log` on every request — the `MIGRATE_KEY` ends up in logs by default.
- **`app/views/error.php` does not exist** — several controllers call `View::render('error', ...)` for access-denied/not-found, which currently hard-crashes with `die("View not found: error")` instead of showing a friendly page.
- **Two audit trail signals that are actually dead**: an `audit_log` table that's never written to, and several `vaccination_*` tables/columns with no active code path (`vaccination_history`, `vaccination_cost_estimates`, `vaccination_schedule_5year`, `vaccine_templates`).

---

## Questions for Interview

1. Should `zootrack/` be documented and secured as part of this Context Tree, or is it out of scope / to be removed from this repo?
2. What's the intended relationship between `MigrateController` and `database/migrate.php` — can one be deleted?
3. Is the missing per-workplace authorization on Warehouse/UrineAnalysis endpoints a known gap to fix, or is "logged in" considered sufficient trust for now?
4. What are the four (or more) "provozy" (workplaces) today, and how often do new ones get added — does adding one require any code change beyond a DB row?
5. Is the vaccination notification/reminder feature planned, abandoned, or partially built elsewhere (external cron)?
6. Are the `.backup`/`.broken`/`.working_backup` view files intentionally kept, and is it acceptable that they currently ship to production via FTP?
7. Is `logic_schema/` meant to be regenerated periodically (e.g., before each Context Tree refresh) or was it a one-time audit?

---

*Discovery complete. Ready for Phase 2 interview.*
