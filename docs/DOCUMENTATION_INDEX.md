# Documentation Index — join-navy-sailor-legacy

Master table of contents for the full documentation set produced for the "Join Navy Sailor" recruitment/application-management platform (admin portal + candidate-facing frontend). All files listed below live under `/home/bs-01692/Personal/MRB/Join Navy/Legacy/join-navy-sailor-legacy/docs/`.

This repo is a **Yii2 2.0 "advanced" project template** application (`common/`, `frontend/`, `backend/`, `console/`) — the sibling architecture to `join-navy-officer-legacy`'s Laravel app. This documentation set mirrors that repo's documentation phases and structure (same Phase 0→15 numbering, same doc types per phase) for consistency across the two legacy codebases, adapted throughout for the real architectural differences between Yii2 and Laravel (no `routes/web.php`, no `FormRequest`/Services layer, two physically separate applications instead of one app split by a role column, etc.).

---

## How to use this for the redesign

If you are a UI/UX designer picking this up cold, don't start at Phase 0 — start here, in this order:

1. **[portal_map.md](01-architecture/portal_map.md)** — read this first, always. It establishes the single fact every other document assumes you already know: `frontend/` and `backend/` are **two physically separate Yii2 applications** (own entry script, own webroot, own session cookie, own identity cookie), not one app split by a `user_type` column the way the sibling officer-repo is. Nothing else in this doc set makes sense until that's internalized.
2. **The eight `deepdive_*.md` files** (listed under Phase 5-10 below), grouped by area — read whichever group matches what you're redesigning:
   - **Frontend / candidate-facing**: `deepdive_frontend_candidate_auth.md`, `deepdive_frontend_eligibility_wizard.md`, `deepdive_frontend_application_wizard.md`, `deepdive_frontend_payment_verify_download.md`.
   - **Backend / admin**: `deepdive_admin_candidate_management.md`, `deepdive_admin_reports.md`, `deepdive_admin_crud_entities.md`, `deepdive_admin_users_auth_settings.md`.
   
   These are the actual ground truth for "what does this screen do, field by field" — each one reads the live controller/view/model source directly and documents exact page inventories, field lists, validation rules, and JS/CSS actually loaded per page. If you are rebuilding one specific screen or flow, start with the matching deep-dive, not any other document.
3. **[modernization_readiness_report.md](06-modernization/modernization_readiness_report.md)** — read this third: it is the cross-cutting synthesis of every other document — architecture-level risk, a consolidated dead-code list, a ranked security list, and the business-logic hotspots that need the most behavioral-parity care in a rebuild.

Once you're designing individual screens, keep these two open as reference, not as reading material:

- **[database_ui_mapping.md](05-workflows/database_ui_mapping.md)** — for every core entity, the DB column → ActiveRecord attribute → view `name="Model[attribute]"` field → validation rule chain. Note that only the `user` table has any migration history in this repo — every other table's schema is reconstructed entirely from model `rules()`, so this is the closest thing to a schema spec that exists.
- **[role_permission_matrix.md](01-architecture/role_permission_matrix.md)** — for who is allowed to see/do what on a given screen (physical app separation + a `user_type` column checked once at login, no RBAC/ACL package).

Everything else (Phase 0 inventories, architecture, folder structure, route traceability, workflow analysis) is supporting evidence the above documents were built from — consult it only when you need to verify a specific low-level claim (an exact file path, line number, or file count).

---

## Phase 0 Inventories

Raw, exhaustive inventories of every layer of the codebase. Evidence base for all later-phase documents.

| File | Covers |
|---|---|
| [repository_inventory.md](00-inventories/repository_inventory.md) | Repo-wide file/size counts across the four Yii2 tiers (`common`/`frontend`/`backend`/`console`), the `environments/`/`init` config-switching mechanism, dead/duplicate code, and the top-line finding that only 2 migrations exist against ~24 real domain models. |
| [route_inventory.md](00-inventories/route_inventory.md) | How Yii2's convention-based `/{controller-id}/{action-id}` URL resolution works with both apps' `urlManager.rules` empty, and each app's `defaultRoute`. |
| [controller_inventory.md](00-inventories/controller_inventory.md) | Every controller (10 frontend, 21 backend, 0 console) with action counts, confirmation there is no `FormRequest`/Services layer (validation lives in model `rules()`), and 4 broken `render()`/missing-class references. |
| [view_inventory.md](00-inventories/view_inventory.md) | All 118 plain-PHP views (39 frontend + 77 backend + 2 shared partials) grouped by module/subfolder, plus the shared layout shells for each app. |
| [javascript_inventory.md](00-inventories/javascript_inventory.md) | All 68 JS files (67 vendor/theme libraries vs. 1 custom application file) and confirmation the whole app loads JS via legacy Yii2 `AssetBundle`/`<script src>` tags — no npm/webpack/Vite pipeline exists anywhere. |
| [component_inventory.md](00-inventories/component_inventory.md) | Every reusable UI pattern — Yii2 `Widget` subclasses, `common/components/` service components, true `render()`/`renderPartial()` partials, and de-facto copy-pasted patterns (Select2 init, GridView admin-list shell, wizard field wrapper, etc.). |
| [css_inventory.md](00-inventories/css_inventory.md) | All 14 CSS/SCSS files across the two competing admin CSS trees plus the candidate-facing "NAVY" template, and inline `<style>` block usage in views. |
| [middleware_inventory.md](00-inventories/middleware_inventory.md) | Yii2's closest equivalents to Laravel middleware — app-level behaviors (`'as <name>' => [...]` in `config/main.php`), controller-level `AccessControl`/`VerbFilter`, and manual in-action guest checks — since Yii2 has no `Kernel.php`/global-middleware-stack layer. |
| [model_inventory.md](00-inventories/model_inventory.md) | All 47 ActiveRecord/form model files (27 `common`, 12 `backend`, 7 `frontend`, 0 `console`) with `rules()`/relations/attribute labels, and the critical finding that only the `user` table has any migration history. |
| [service_inventory.md](00-inventories/service_inventory.md) | The scattered Yii2 equivalents of a Services layer — `R2Storage` application component, `SSLPayment` static gateway class, `common/static/` helper classes — since this codebase has no `app/Services`/`app/Classes`/`app/Jobs` directory at all. |

## Phase 1 Architecture

| File | Covers |
|---|---|
| [architecture_document.md](01-architecture/architecture_document.md) | Framework/language versions (Yii2 `~2.0.45`, PHP `>=7.4`), the four-tier "advanced template" application architecture, and cited cross-references into every Phase 0 inventory document. |

## Phase 2 Folder Structure

| File | Covers |
|---|---|
| [folder_structure_documentation.md](02-structure/folder_structure_documentation.md) | Folder-by-folder breakdown of the entire repository tree — purpose, responsibilities, dependencies, and related modules for `common/`, `frontend/`, `backend/`, `console/`, and every key nested directory. |

## Phase 3 Portal Map

| File | Covers |
|---|---|
| [portal_map.md](01-architecture/portal_map.md) | The foundational architectural fact: `frontend/` and `backend/` are two physically separate `yii\web\Application` instances (own entry script, session cookie, identity cookie), sharing only the one `common\models\User` table — not one app split by a `user_type` column. |

## Phase 4 Route Traceability

| File | Covers |
|---|---|
| [traceability_matrix.md](03-traceability/traceability_matrix.md) | Route (implicit `controller-id/action-id`) → Controller::action → Model/Service → View → JS/CSS asset-bundle trace for all 171 live, routable actions across both apps, with dead/duplicate/broken code explicitly flagged rather than traced. |

## Phase 5-10 Functional Deep Dives

Screen-by-screen walkthroughs of each major functional area — page inventories, exact field lists, validation rules, and JS/CSS actually loaded per page.

### Frontend (candidate-facing)

| File | Covers |
|---|---|
| [deepdive_frontend_candidate_auth.md](04-deep-dives/deepdive_frontend_candidate_auth.md) | Candidate sign up, sign in, logout, change/reset password screens (`CandidateController`) — and the discovery that a second, dead Yii2-scaffold auth flow (`SiteController`) coexists unreachable and functionally broken alongside the real one. |
| [deepdive_frontend_eligibility_wizard.md](04-deep-dives/deepdive_frontend_eligibility_wizard.md) | The public, unauthenticated 3-step pre-application eligibility checker wizard (personal → academic → eligible department) and its encrypted hand-off into candidate signup. |
| [deepdive_frontend_application_wizard.md](04-deep-dives/deepdive_frontend_application_wizard.md) | The parallel Sailor and DE-Sailor multi-step application wizards (payment → academic info → personal info → preview → complete), and the lack of any centralized phase-gating middleware (every action re-implements its own inline phase check). |
| [deepdive_frontend_payment_verify_download.md](04-deep-dives/deepdive_frontend_payment_verify_download.md) | The live SSLCommerz payment gateway flow, My Applications page, and PDF generation/public-verification/download-form actions, including the reversible custom-cipher `slug` scheme used as pseudo-authorization. |

### Backend (admin)

| File | Covers |
|---|---|
| [deepdive_admin_candidate_management.md](04-deep-dives/deepdive_admin_candidate_management.md) | Admin Sailor / DE-Sailor / DE-Sailor-Branch candidate management and reference-candidate sub-flows, including the fully broken `DeSailorBranchController` (its entire view directory is missing on disk). |
| [deepdive_admin_reports.md](04-deep-dives/deepdive_admin_reports.md) | The three bulk reporting/export controllers (general reports, DE-Sailor reports, audit-log viewer) — 35 actions total, none with per-controller access control beyond the app-level "any authenticated user" gate. |
| [deepdive_admin_crud_entities.md](04-deep-dives/deepdive_admin_crud_entities.md) | The 10 simple Gii-scaffolded admin CRUD entities (candidate designation, districts, eligibility, batch/batch-configuration, exam centers, subjects, unions, upozilas) and their shared conventions. |
| [deepdive_admin_users_auth_settings.md](04-deep-dives/deepdive_admin_users_auth_settings.md) | Admin users, login, and login-log screens, plus the shared "Hyper" admin layout shell and its double-jQuery-loading asset bundle. |

## Phase 11 Roles & Permissions

| File | Covers |
|---|---|
| [role_permission_matrix.md](01-architecture/role_permission_matrix.md) | The home-grown, package-free authorization scheme built on physical app separation + a `user_type` column checked exactly once at login + sparse controller-level `AccessControl` — no `yii\rbac`/ACL package is used anywhere. |

## Phase 13-14 Workflows & DB Mapping

| File | Covers |
|---|---|
| [workflow_analysis.md](05-workflows/workflow_analysis.md) | Every multi-step business workflow (eligibility-precheck→signup hand-off, Sailor/DE-Sailor application submission, payment, admin review/reference-candidate/cancel-application, roll-number/exam-center allocation, reporting/export) traced through the live route set, with Mermaid diagrams. |
| [database_ui_mapping.md](05-workflows/database_ui_mapping.md) | DB column → ActiveRecord attribute → view `name="Model[attribute]"` field → validation rule chain for every core entity (Sailors, DeSailors, User, batches, centers, eligibility, districts/unions/upozilas), flagging that only `user` has migration history — every other table's schema is reconstructed from `rules()` alone. |

## Phase 15 Modernization Readiness

| File | Covers |
|---|---|
| [modernization_readiness_report.md](06-modernization/modernization_readiness_report.md) | Synthesis of every Phase 0-14 document into a single rebuild-readiness assessment — the two-physically-separate-apps architecture, fat-controller/fat-ActiveRecord-model business logic with no Services layer, consolidated dead code, and the security/access-control gaps a rebuild needs to account for. |
