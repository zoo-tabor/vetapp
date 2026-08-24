# Phase 2: Domain Expert Interview

**Started:** 2026-08-24
**Interviewer:** Claude Code
**Expert:** janstich (project owner)

---

## Interview Session 1 - 2026-08-24

### Category: Architectural Gotchas

---

#### Q1: zootrack/ status and auth

**Question:** Is `zootrack/` actively maintained and part of this project going forward, and is its apparent lack of authentication (per discovery.md, based on logic_schema/ audit) a real security gap to fix, or out of scope?

**Answer:** zootrack is actively maintained and used. Accessing it requires login. The front-end is currently missing email/phone fields for institutions that the backend database already has — the current DB dump lives in vetapp/database.

**Verified:** ❌ Contradicted (discovery.md claim was stale, not the user's answer)
- `zootrack/index.php` calls `auth_check.php` → `zt_require_login_page()` (redirects unauthenticated visitors to `/login`).
- `zootrack/api.php` calls `zt_require_login_api()` (401 JSON for unauthenticated callers).
- `zootrack/auth_check.php` reuses VetApp's own PHP session (`$_SESSION['user_id']`, `$_SESSION['role']`, `$_SESSION['zootrack_edit']`) — same host/PHP pool as the main app, so no separate login exists by design.
- No `Access-Control-Allow-Origin` header found anywhere in `zootrack/`.
- **Conclusion:** the `logic_schema/` audit finding "no authentication at all" is outdated — auth was added to zootrack after that audit (dated 2026-06-26). `docs/context-tree-build/discovery.md` should be corrected/annotated rather than trusted as current.

**Value:** High — corrects a stale/incorrect security finding that would otherwise propagate into final docs as a false alarm. Also surfaces a real, separate small gap: institution email/phone fields exist in the DB but not in the zootrack front-end.

**Security Critical:** No (auth already in place; original discovery claim was wrong)

---

#### Q2: Duplicate migration runner

**Question:** Is `database/migrate.php` still used for anything (e.g. run manually via CLI/SSH), or is it dead code safe to delete now that `MigrateController` handles `/migrate?key=...`?

**Answer:** Safe to delete — migrations are done via `http://vetapp.zootabor.eu/migrate?key=...`.

**Verified:** ✅ Confirmed
- `index.php:804-806` — only route registered for `/migrate` requires `MigrateController.php`.
- No other reference to `database/migrate.php` found in routing.

**Value:** Medium — actionable cleanup item (dead code / tech debt), not a documentation insight per se. Worth noting as a to-do rather than a CLAUDE.md fact.

**Security Critical:** No

---

#### Q3: Missing per-workplace authorization on Warehouse/UrineAnalysis

**Question:** Discovery found `WarehouseController` (create/update/movement/consumption) and `UrineAnalysisController::updateResult` only call `Auth::requireLogin()`, not `hasPermission`/`hasAccess`, unlike `BiochemistryController::updateResult` which checks correctly. Is this a known/intentional gap, or a bug to fix?

**Answer:** It's a bug and should be fixed.

**Verified:** ✅ Confirmed as a real, acknowledged gap (owner's own assessment — matches discovery.md's code-level finding that these controllers diverge from the documented "always check hasAccess" pattern in CLAUDE.md).

**Value:** High — security-critical, actionable bug, and clarifies the correct pattern is universal (Biochemistry is the reference implementation to copy into Warehouse/UrineAnalysis).

**Security Critical:** Yes ⚠️ — IDOR: any logged-in user can currently write to warehouse/urine-analysis data for workplaces they don't have permission for.

---

#### Q4: Provozy (workplaces) — complete list and how to add one

**Question:** Is ZOO Tábor / Babice / Lipence / Deponace the complete current list of workplaces, and does adding a new one ever require a code change, or is it purely a DB row insert into `workplaces`?

**Answer:** That's the complete current list and we shouldn't ever need to add a new one. The user tested empirically and confirmed: inserting a row into `workplaces` is sufficient — a new workplace appears in every module automatically.

**Verified:** ✅ Confirmed (verified empirically by the domain expert, not just by static code reading — strong confidence)

**Value:** High — settles an open discovery question and confirms `workplaces` is a clean, code-free extension point in practice (even though not expected to be used again). Good to document so a future "how do I add a workplace" question has a definitive answer.

**Security Critical:** No

---

#### Q5: Vaccination reminder system — planned, abandoned, or external?

**Question:** `VaccinationPlan::getNotificationDue`/`markNotificationSent` and `notification_sent_*` columns exist with no caller/cron job. Is this planned, abandoned, or handled outside the codebase?

**Answer:** Currently checked manually against the vaccination plan page (no real production data yet, only test data). The notification system is a planned feature meant to be wired up as a cron job — not implemented yet, not abandoned.

**Verified:** ✅ Confirmed as consistent with discovery.md (schema/methods exist, no scheduler wired up) — matches "planned, not yet built" rather than dead code.

**Value:** Medium-High — important context: don't flag the vaccination system as dead code or a bug in final docs; it's an intentional in-progress feature. Also notable: the vaccination module is pre-production (test data only).

**Security Critical:** No

---

## Interview Summary

**Total Questions Asked:** 5
**Sessions:** 1
**Categories Completed:** Architectural Gotchas (5 Q&A — zootrack auth, duplicate migration runner, Warehouse/Urine authz bug, workplaces extensibility, vaccination reminders)
**Categories Remaining:** Terminology Traps, Business Context & Workflows, Common Pitfalls, Pain Points & What Takes Longest to Explain

**High-Value Insights:** 5
**Security-Critical Items:** 1 (Warehouse/UrineAnalysis missing per-workplace authorization — confirmed bug)

**Status:** Can resume - continue with Terminology Traps category (or Business Context)

---

*Interview notes are append-only. Each session adds to this file.*

---

### Category: Terminology Traps

---

#### Q6: Czech/English terminology gaps beyond "provoz"

**Question:** Beyond *provoz* → `Workplace`, is there any other Czech/English terminology gap that trips people up — in UI labels, section names, or how staff refer to features verbally vs. what's in the code?

**Answer:** The whole app's UI is in Czech, since it's built for a Czech zoological garden's staff — staff refer to every feature by its Czech name.

**Verified:** ✅ Confirmed — consistent with CLAUDE.md's Czech section labels table (e.g. "Seznam zvířat", "Parazitologie", "Analýza moči") and view templates.

**Value:** Medium — confirms the terminology gap isn't isolated to "provoz"; it's systemic (Czech UI / English code throughout), which is itself worth stating plainly in the Context Tree so future AI sessions don't get confused searching for English UI strings that don't exist. `urine`/`urineanalysis` (Q-seed from discovery) remains the one concrete internal-key mismatch found so far.

**Security Critical:** No

---

### Category: Business Context & Workflows

---

#### Q7: User roles and primary workflows

**Question:** Who are the actual day-to-day users (vets, zookeepers, admin staff?), and what's the primary workflow / happy path for each?

**Answer:** Three user types: **keepers** (zookeepers) — check what needs doing, update inventory (warehouse); **vets** — input examination/lab results (biochemistry, urine, parasitology); **admins** — oversee the system and manage permissions (who can access which workplace/section).

**Verified:** ✅ Confirmed — matches `Section` enum (`animals`, `parasitology`, `biochemistry`, `urine`, `vaccination`, `warehouse`) and the `user_permissions` per-workplace/per-section model in CLAUDE.md. Warehouse aligns with keeper inventory duties; parasitology/biochemistry/urine/vaccination align with vet result-entry duties; admin role bypasses all permission checks per CLAUDE.md.

**Value:** High — first clear mapping of user role → which sections they actually touch. Useful for Context Tree: keepers ≈ warehouse-focused, vets ≈ lab-results-focused, admins ≈ permission management, not day-to-day data entry.

**Security Critical:** No

---

### Category: Common Pitfalls

---

#### Q8: Common mistakes / hardest things to explain

**Question:** What's the most common mistake made while developing this codebase, or the thing that takes longest to explain to someone new? (Asked twice — pushed for a concrete example.)

**Answer:** Two recurring pain points: (1) migrations weren't written to be idempotent — re-running one against a DB where it already applied breaks instead of no-oping; (2) permissions weren't accurately/consistently enforced across all modules (same root issue as Q3 — Warehouse/UrineAnalysis skip the hasAccess checks that other controllers use).

**Verified:** ✅ Confirmed
- `database/migrations/013_add_zootrack_edit_to_users.php` — raw `ALTER TABLE users ADD COLUMN zootrack_edit ...` with no existence guard; MySQL/MariaDB `ADD COLUMN` has no portable `IF NOT EXISTS` in the versions this stack targets, so re-running this migration on an already-migrated DB throws a duplicate-column error rather than skipping cleanly.
- The `MigrateController`/`/migrate?key=` runner tracks executed migrations in a `migrations` table (per CLAUDE.md) so normal operation avoids double-running — the idempotency risk surfaces specifically if that tracking table is ever out of sync with actual schema state (e.g. manual DB changes, restored backup, or migration re-run after a failed partial apply).
- Permission inconsistency: same finding as Q3 (Warehouse/UrineAnalysis missing hasAccess checks).

**Value:** High — two concrete, code-confirmed gotchas for onboarding: (1) never assume migrations are safe to re-run blind — check the `migrations` tracking table state first; (2) permission enforcement is NOT uniformly applied across controllers despite CLAUDE.md's stated pattern — always verify a given controller actually calls hasAccess/hasPermission rather than assuming it does because the pattern is documented.

**Security Critical:** Partially — the permission half is the same confirmed IDOR from Q3.

---

### Category: Pain Points & What Takes Longest to Explain

---

#### Q9: What to emphasize first when onboarding a new developer

**Question:** If onboarding a new developer, what's the single thing you'd emphasize first — the thing that saves the most time/trouble if known up front?

**Answer:** Read the comments — everything in the codebase is meant to be cleanly explained inline. Also check `deepwiki.com/zoo-tabor/vetapp` — an auto-generated wiki that's had some manual adjustments made over the generated content. The app is plain PHP with **no Composer available** (hosting is WEDOS, which doesn't support it). Be careful with CSS responsiveness for mobile — it's almost non-existent right now.

**Verified:** ✅ Confirmed
- No Composer: matches discovery.md (`no composer.json`/`vendor/`) and `.htaccess` comment "VEDOS Hosting Compatible" (note: htaccess spells it "VEDOS", user said "WEDOS" — likely the same host, hosting-provider name inconsistency, not a code issue).
- Inline comments: consistent with code read so far (e.g. detailed doc-comments in `zootrack/index.php`, `zootrack/auth_check.php`).
- Mobile CSS gap not independently verified (no viewport/responsive audit performed) — taken as domain-expert-reported, high-confidence.

**Value:** High — three concrete onboarding directives: (1) trust inline comments as primary in-code documentation, (2) `deepwiki.com/zoo-tabor/vetapp` is a secondary reference worth linking from the Context Tree, (3) Composer is categorically unavailable on this host (WEDOS/VEDOS) — don't suggest adding Composer-based dependencies. Also flags mobile responsiveness as a known, unaddressed gap — useful context before someone assumes mobile support exists.

**Security Critical:** No

---

## Interview Summary

**Total Questions Asked:** 9
**Sessions:** 1
**Categories Completed:** Architectural Gotchas (5 Q&A), Terminology Traps (1 Q&A), Business Context & Workflows (1 Q&A), Common Pitfalls (1 Q&A), Pain Points & What Takes Longest to Explain (1 Q&A)
**Categories Remaining:** None — all 5 categories covered

**High-Value Insights:** 8
**Security-Critical Items:** 1 confirmed bug (Warehouse/UrineAnalysis missing per-workplace authorization — surfaced in both Q3 and Q8), 1 corrected false-positive (zootrack auth, Q1)

**Status:** Interview complete — comprehensive coverage across all 5 categories. Ready for Phase 3 documentation generation.

---

*Interview notes are append-only. Each session adds to this file.*