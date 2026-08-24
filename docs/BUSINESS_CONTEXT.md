---
name: Business Context
description: Domain knowledge, workflows, and business rules for vetapp
type: reference
scope: repo
source: build
verified: 2026-08-24
---

# Business Context

**Purpose:** Domain knowledge, user workflows, and business rules

**Read this when:** You need to understand what the system does and why

---

## What This System Does

Vetapp is a management system for a Czech zoological garden, used by on-site staff to track animal records, lab results, vaccinations, and warehouse inventory across the zoo's multiple physical sites. The entire UI is in Czech — staff refer to every feature by its Czech name (e.g. "Analýza moči" for urine analysis, "Parazitologie" for parasitology), even though the codebase itself is written in English.

**Core problem solved:** Coordinating animal health record-keeping (examinations, lab results, vaccinations) and supply management (warehouse inventory) across several physically separate zoo sites, with per-site, per-module access control.

**Users:** Three role types with distinct day-to-day workflows (see below).

---

## Primary Workflows

### Keepers (zookeepers)
1. **Check what needs doing** at their assigned workplace(s).
2. **Update inventory** — record stock movements/consumption in the warehouse module.
   - Code: `app/controllers/WarehouseController.php` (`addMovement`, `setConsumption`, `saveInventory`)
   - Business rule: requires `warehouse` section `edit` permission for the specific workplace (`userCan($workplaceId, 'warehouse', 'edit')`).

### Vets
1. **Input examination/lab results** — parasitology, biochemistry/hematology, urine analysis.
   - Code: `app/controllers/BiochemistryController.php`, `app/controllers/UrineAnalysisController.php`, parasitology controllers
   - Business rule: requires `edit` permission on the relevant section for the animal's workplace.
2. **Bulk-import lab results** via `.ldt` files exported from lab equipment, rather than manual entry, for biochemistry/hematology.
   - Code: `app/controllers/BiochemistryImportController.php`; format reference in `LDT/LDT_handling_documentation.md`

### Admins
1. **Oversee the system and manage permissions** — who can access which workplace/section, at what level (view/edit).
   - Code: `User::hasPermission`, `user_permissions` table, admin settings views
   - Business rule: admins bypass all permission checks automatically (built into the model layer, not re-implemented per controller) — admins are not expected to be doing day-to-day data entry themselves, their role is permission management and oversight.

---

## Business Rules

### Workplace access is the primary isolation boundary
**Rule:** Every user's access to any section (warehouse, biochemistry, urine, etc.) is scoped per-workplace via `user_permissions` (`workplace_id`, `section`, `can_view`, `can_edit`).

**Why it exists:** The zoo operates across multiple physically separate sites (ZOO Tábor, Babice, Lipence, Deponace) with staff who may only work at one or a subset of them; data and duties shouldn't cross site boundaries by default.

**Code enforcement:** `User::hasPermission()`, `Workplace::hasAccess()` — see root `CLAUDE.md` Permission System section for the full pattern.

**Edge cases:** Admins bypass this entirely. New workplaces require zero code changes (just a DB row) — see `docs/ARCHITECTURE.md`.

### Vaccination reminders are a planned, not-yet-active feature
**Rule (current state, not aspirational):** `VaccinationPlan::getNotificationDue()`/`markNotificationSent()` exist, and `notification_sent_*` columns exist in schema, but no cron job or scheduler currently calls them.

**Why it exists:** Intended to eventually notify staff when a vaccination is due, checked manually against the vaccination plan page today.

**Code enforcement:** N/A yet — deliberately unwired, not a bug. The vaccination module currently only holds test data, not production data.

---

## Domain Concepts

### "Provoz" (workplace)
**What it is:** Czech for "site/facility/operation" — what staff and the domain expert call each physical zoo location. Maps 1:1 to the `Workplace` model / `workplaces` table in code.

**Current instances:** ZOO Tábor, Babice, Lipence, Deponace — confirmed as the complete, stable list; not expected to grow.

**Code:** `app/models/Workplace.php`, `workplaces` table

---

## User Roles & Permissions

| Role | Capabilities | Code Enforcement |
|------|-------------|------------------|
| Keeper (zookeeper) | Warehouse inventory: view/record stock movements, consumption, inventories, at workplaces they have `warehouse` permission for | `WarehouseController`, `userCan($workplaceId, 'warehouse', ...)` |
| Vet | Enter/view lab results (parasitology, biochemistry, urine) for animals at workplaces they have the relevant section permission for | `BiochemistryController`, `UrineAnalysisController`, `User::hasPermission()` |
| Admin | Full access to all workplaces/sections; manages `user_permissions` grants for other users | `Auth::isAdmin()` short-circuit in all permission-check methods |

---

*Business context captured: 2026-08-24*
*Source: Domain expert interview*
