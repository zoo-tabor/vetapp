---
name: Architecture Reference
description: Non-obvious architectural patterns and constraints for vetapp
type: reference
scope: repo
source: build
verified: 2026-08-24
---

# Architecture Reference

**Purpose:** Non-obvious architectural patterns, decisions, and constraints

**Read this when:** You're confused about how systems interact or why things are structured a certain way

---

## Technology Stack

- **Language:** PHP 8.1+ (not documented in the root `CLAUDE.md` — inferred from `sanitizeString()`/`FILTER_SANITIZE_STRING` no longer existing in `app/helpers/functions.php`, which was removed in PHP 8.1)
- **No Composer, no autoloader** — hosting is WEDOS (`.htaccess` calls it "VEDOS"), which doesn't support Composer. Every controller method manually `require_once`s the models it needs at the top of the method; a missing `require_once` is a runtime fatal, not a build-time error.
- **Database:** MySQL/MariaDB via PDO, no ORM.

---

## Critical Architectural Patterns

### `zootrack/` — nested independent sub-application sharing the parent's auth session

**What it is:** A second, self-contained application living inside this repo, with its own `.git`, `.env`, `.htaccess`, and `.github/workflows/deploy.yml` — a genuinely separate deployable unit, not a module of the main app.

**How it works:** Despite being a separate app, it does *not* have its own login system — `zootrack/auth_check.php` reads the main app's `$_SESSION['user_id']`, `$_SESSION['role']`, and a `$_SESSION['zootrack_edit']` flag, meaning both apps run in the same PHP session pool on the same host. `zt_require_login_page()` redirects unauthenticated visitors to `/login` (the main app's login), `zt_require_login_api()` returns 401 JSON.

**Code evidence:** `zootrack/auth_check.php:43,52`; `database/migrations/013_add_zootrack_edit_to_users.php` (adds the global, non-workplace-scoped `zootrack_edit` column to `users`, with a comment explaining ZooTrack data is global rather than workplace-scoped).

**Critical rule:** Don't assume `zootrack/` is either fully independent (it isn't — it depends on the main app's session/login) or fully integrated (it isn't — separate `.git`, separate deploy workflow, separate data domain: `institutions`/`zootrack_*`/`geocache` tables, unrelated to `animals`/`examinations`).

**What breaks if violated:** Treating it as unauthenticated (per a stale prior audit) would incorrectly flag it as an open write endpoint when it's actually gated by the shared session. Treating it as part of the main app's routing/permission system would be wrong too — it has no `Section`/`user_permissions` integration.

### LDT lab-import pipeline

**What it is:** `LDT/` contains real sample `.ldt` files (German "Labordatenträger" — a structured lab-result export format used by veterinary/medical labs) plus `LDT/LDT_handling_documentation.md`, a detailed format guide.

**How it works:** `BiochemistryImportController` consumes these files for bulk biochemistry/hematology import. This is the actual production import path — not a generic CSV template you might expect from the section name.

**Code evidence:** `app/controllers/BiochemistryImportController.php`, `LDT/LDT_handling_documentation.md`

**Critical rule:** If asked to add or debug bulk lab-result import, start with `LDT_handling_documentation.md` and the sample files, not by inventing a new import format.

### `workplaces` table as a code-free extension point

**What it is:** `workplace_id` is threaded through nearly every permission check and business table as the tenant-like isolation boundary (per-user, per-workplace, per-section grants in `user_permissions`).

**How it works:** Confirmed empirically by the domain expert (not just inferred from code): inserting a new row into the `workplaces` table is sufficient — the new workplace appears automatically in every module (permissions UI, warehouse, lab sections, etc.) with zero code changes required.

**Critical rule:** Do not assume adding a workplace requires touching `User::SECTIONS`, routing, or any hardcoded workplace list — those are all about sections/permissions, not about which workplaces exist. (Note: this is not expected to be exercised again — the current 4 workplaces, ZOO Tábor/Babice/Lipence/Deponace, are considered the complete, final list.)

### Migrations are not idempotent — the tracking table is the only safety net

**What it is:** `database/migrations/NNN_name.php` files run raw DDL (e.g. `013_add_zootrack_edit_to_users.php`: `ALTER TABLE users ADD COLUMN zootrack_edit ...`) with no existence guard. MySQL/MariaDB has no portable `ADD COLUMN IF NOT EXISTS` in the versions this stack targets.

**How it works:** The `/migrate?key=...` runner (`MigrateController.php`) tracks executed migrations in a `migrations` table and skips already-applied ones — this is the *only* thing preventing a double-run. If that tracking table is ever out of sync with actual schema (manual DB edit, restored backup, a failed partial apply), re-running the runner will hard-fail with a duplicate-column/table error rather than skipping cleanly.

**Code evidence:** `database/migrations/013_add_zootrack_edit_to_users.php`; `MigrateController.php` (migrations table tracking, referenced from root `CLAUDE.md`)

**Critical rule:** Before manually re-running migrations against a database whose state you're unsure of, check the `migrations` table's recorded rows against the actual schema first.

### Historical: static-analysis audits go stale fast — verify, don't inherit

**What it is:** `logic_schema/` is a prior static-analysis snapshot (generated 2026-06-26, 10:24) with machine-readable `findings.json`/`routes.json`/`database.json`/`functions.json`.

**How it works — and why this matters:** File timestamps show `app/helpers/functions.php`, `WarehouseController.php`, and `UrineAnalysisController.php` were all modified 2026-06-26 between 15:54 and 15:56 — hours after the audit ran. Comparing the audit's findings against current code confirms at least three of its top-severity/dead-code items are already resolved:
1. The missing per-workplace authorization on `WarehouseController`/`UrineAnalysisController::updateResult` (the audit's top security finding) — every write method in both controllers now calls `userCan($workplaceId, $section, $perm)` (`app/helpers/functions.php:335`).
2. `WarehouseController::central()`'s `ValueError` crash on `str_repeat('?,', -1)` for a user with zero accessible workplaces — now explicitly guarded (`WarehouseController.php:119-131`, with an inline comment referencing the crash it avoids).
3. `database/migrate.php` (flagged as a duplicate migration runner) and the `app/views/animals/list.php.backup`/`.broken`/`.working_backup` files (flagged as shipping to production) no longer exist in the repo at all.

**Critical rule:** Treat `logic_schema/` as a *source of questions to re-verify against current code*, never as a current-state fact sheet. This applies to any prior audit or interview note in this Context Tree, too — see root `CLAUDE.md`'s Common Pitfalls for the operative rule.

**What breaks if violated:** Re-flagging an already-fixed bug as open (wastes investigation time), or — worse — assuming a *different*, currently-real gap doesn't exist because "the audit didn't mention it and the audit is thorough." Always check the actual code path in question.

---

## System Interactions

### Main app ↔ `zootrack/`

**Relationship:** Shared PHP session/login only — no shared routing, models, or `Section`/permission system.

**Data flow:** Both read/write `$_SESSION` set by the main app's `Auth`. `zootrack/` has its own DB tables (`institutions`, `zootrack_*`, `geocache`) and its own `.env` DB connection — schema-independent from the main app's `animals`/`examinations`/`workplaces` tables.

**Code evidence:** `zootrack/auth_check.php`, `zootrack/.env_example`

**Gotchas:** Deploying `zootrack/` changes — check whether `zootrack/.github/workflows/deploy.yml` is a separate CI pipeline from the main app's `.github/workflows/deploy.yml`, since they're independent git histories nested in one working tree.

---

## Common Architectural Mistakes

1. **Assuming every controller enforces per-workplace authorization the way the documented pattern in root `CLAUDE.md` describes.**
   - What happens: As of the 2026-06-26 audit (now fixed — see Historical pattern above), two controllers didn't. The lesson generalizes: the documented pattern is the *intended* convention, not a guarantee every method follows it.
   - How to avoid: Read the specific method before relying on it having authorization checks.
   - How to verify: Grep the controller for `hasAccess`/`hasPermission`/`userCan` and confirm it's called before any DB write, not just present somewhere in the file.

2. **Re-running a migration without checking the `migrations` tracking table first.**
   - What happens: Hard failure (duplicate column/table) instead of a clean no-op, because migrations aren't written to be idempotent.
   - How to avoid: Check `SELECT * FROM migrations` (or equivalent) against actual schema state before manual intervention.

---

*Architecture verified: 2026-08-24*
*Source: Code analysis + domain expert interview*
