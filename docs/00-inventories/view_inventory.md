# View Inventory — `frontend/views/`, `backend/views/`, `frontend/components/views/`

**Total: 118 files on disk** (39 `frontend/views/*.php` + 77 `backend/views/*.php` + 2 `frontend/components/views/*.php`).

Yii2 2.0 advanced template: two applications (`frontend/` = public candidate portal, `backend/` = admin panel) sharing one `common/` model layer. Views are plain PHP (`$this->render(...)`, `$this->renderPartial(...)`, `ActiveForm`), not Blade.

| Module | Subfolder | File count | Purpose |
|---|---|---|---|
| frontend | `candidate/` | 4 | Candidate account auth (login, signup, password reset/change) |
| frontend | `check-eligibility/` | 3 | Public eligibility-checker wizard (no login required) |
| frontend | `de-sailor/` | 10 | Direct-Entry (DE) Sailor application wizard + PDF/download views |
| frontend | `layouts/` | 1 | Shared public-site shell |
| frontend | `my-application/` | 1 | Logged-in candidate's application list/status page |
| frontend | `online-payment/` | 1 | Payment entry point (unmodified Yii2 scaffold stub) |
| frontend | `sailor-candidate/` | 9 | General Sailor Candidate application wizard + PDF/download views (parallel track to `de-sailor/`) |
| frontend | `site/` | 10 | Static/marketing pages, generic auth (email verification, password reset) |
| frontend | `components/views/` | 2 | Shared partials rendered by widgets (step indicator, support/help text) |
| backend | `can-designation/` | 3 | CRUD — candidate designations |
| backend | `de-sailor-report/` | 6 | Reports scoped to Direct-Entry Sailor candidates |
| backend | `de-sailors/` | 8 | CRUD/review of Direct-Entry Sailor candidates + reference-candidate sub-flow |
| backend | `districts/` | 3 | CRUD — districts |
| backend | `eligibility/` | 3 | CRUD — eligibility rule configuration |
| backend | `layouts/` | 5 | Shared admin shells + partials |
| backend | `log-report/` | 1 | Site activity log report |
| backend | `report/` | 15 | General reporting (candidate filter, center/district candidate lists, monitoring, payment, exam-date checks) |
| backend | `sailor-batch-configuration/` | 3 | CRUD — batch configuration rules |
| backend | `sailor-batchs/` | 3 | CRUD — recruitment batches |
| backend | `sailor-cent-dist-mapping/` | 3 | CRUD — exam center ↔ district mappings |
| backend | `sailor-centers/` | 3 | CRUD — exam centers |
| backend | `sailors/` | 6 | CRUD/review of general Sailor candidates + reference-candidate sub-flow |
| backend | `site/` | 3 | Admin dashboard, login, error page |
| backend | `subjects/` | 3 | CRUD — exam subjects |
| backend | `unions/` | 3 | CRUD — unions (admin. divisions) |
| backend | `upozilas/` | 3 | CRUD — upazilas (admin. divisions) |
| backend | `user/` | 3 | CRUD — admin users |

**Layout files (shared shells everything else extends/renders into):**

| File | Used by | Includes |
|---|---|---|
| `frontend/views/layouts/mainNavy.php` (257 lines) | All `frontend/*` views (default layout, set in `frontend/config/main.php`) | `common\widgets\Alert`; site header/footer/nav markup inline (no separate partials) |
| `backend/views/layouts/admin.php` (69 lines) | Almost all `backend/*` views (default layout, set in `backend/config/main.php`) | `$this->render('top_bar')`, `$this->render('left_side_menu')` |
| `backend/views/layouts/top_bar.php` (68 lines) | Partial included by `layouts/admin.php` — top navbar | — |
| `backend/views/layouts/left_side_menu.php` (183 lines) | Partial included by `layouts/admin.php` — sidebar nav | — |
| `backend/views/layouts/blank.php` (33 lines) | `backend/controllers/SiteController.php` sets `$this->layout = 'blank'` for the login action — chromeless shell | — |
| `backend/views/layouts/main.php` (82 lines) | Unused leftover Yii2-scaffold default layout — uses `Nav::widget`/`Breadcrumbs::widget`; no controller references it (`admin`/`blank` are the only layouts actually assigned) | `common\widgets\Alert` |

Note: `de-sailor/candidate/application_form_pdf.php`, `de-sailor/candidate/application_verification_pdf.php`, `sailor-candidate/candidate/application_form_pdf.php`, `sailor-candidate/candidate/application_verification_pdf.php`, and all `backend/views/*/pdf/*.php` files are **standalone full HTML documents** (own `<!doctype html>`) — they render outside the normal layout since they're captured to PDF (mPDF/DomPDF-style), not served as site pages.

---

## Architectural note: two parallel candidate tracks

The frontend runs **two near-duplicate application pipelines** side by side, each with its own controller and view folder:

- `frontend/controllers/DeSailorController.php` → `frontend/views/de-sailor/*` — **Direct-Entry (DE) Sailor** track (adds a diploma/trade education section not present in the other track)
- `frontend/controllers/SailorCandidateController.php` → `frontend/views/sailor-candidate/*` — **general Sailor Candidate** track

File-for-file the two folders mirror each other (`academic_info.php`, `application_preview.php`, `application_verify_preview.php`, `payment.php`, `personal_info.php`, `candidate/application_form_download.php`, `candidate/application_form_pdf.php`, `candidate/application_verification_pdf.php`, `candidate/my_application.php`), with `de-sailor/` versions running ~10–15% longer due to the extra diploma/trade fields. The same split repeats on the backend: `backend/views/report/*` vs `backend/views/de-sailor-report/*`, and `backend/views/sailors/*` vs `backend/views/de-sailors/*`. Some backend pairs are byte-identical duplicates (`report/pdf/candidate_filter_pdf.php` = `de-sailor-report/pdf/candidate_filter_pdf.php`; `report/pdf/payment_report_pdf.php` = `de-sailor-report/pdf/payment_report_pdf.php`; `sailors/reference/update_reference_candidate.php` = `de-sailors/reference/update_reference_candidate.php`) — copy-pasted rather than shared/refactored.

---

## frontend/views/candidate/ (4 files)
Candidate account authentication, separate from the generic `site/` auth pages.

| File | Purpose | Layout | Size |
|---|---|---|---|
| change_password.php | Candidate change-password form | mainNavy | small (38) |
| login.php | Candidate login form | mainNavy | small (54) |
| request_password_reset.php | Candidate forgot-password request form | mainNavy | small (57) |
| sign_up.php | Candidate signup form | mainNavy | medium (172) |

## frontend/views/check-eligibility/ (3 files)
Public multi-step eligibility checker (no login) — determines which department/branch a candidate qualifies for before they apply.

| File | Purpose | Layout | Size |
|---|---|---|---|
| academic_info.php | Eligibility checker step — academic info | mainNavy | large (409) |
| eligible_department.php | Eligibility checker — result page listing eligible department(s) | mainNavy | medium (201) |
| personal_info.php | Eligibility checker step — personal info (first step) | mainNavy | large (464) |

## frontend/views/de-sailor/ (10 files)
Direct-Entry Sailor application wizard: personal → academic → payment → preview → verify, plus post-submit download/PDF/list views.

| File | Purpose | Layout | Size |
|---|---|---|---|
| academic_info.php | Application wizard step — academic info (DE track, incl. diploma/trade) | mainNavy | medium (275) |
| application_preview.php | Application preview before final submission | mainNavy | large (741) |
| application_verify_preview.php | Post-submission verification/preview page | mainNavy | large (603) |
| candidate/application_form_download.php | Download-application-form entry page (lookup by app ID or personal info) | mainNavy | small (99) |
| candidate/application_form_pdf.php | Standalone HTML — application form rendered to PDF | — (standalone) | large (1014) |
| candidate/application_verification_pdf.php | Standalone HTML — verification slip rendered to PDF | — (standalone) | large (609) |
| candidate/my_application.php | Candidate's own DE application status page | mainNavy | medium (125) |
| index.php | Unmodified Yii2 scaffold stub (`de-sailor/index`) — dead/default action view | mainNavy | tiny (9) |
| payment.php | Application wizard step — payment | mainNavy | medium (144) |
| personal_info.php | Application wizard step — personal info (first step) | mainNavy | large (1074) |

## frontend/views/layouts/ (1 file)
| File | Purpose | Extends | Size |
|---|---|---|---|
| mainNavy.php | Main public-site layout shell (header/nav/footer), default for all `frontend/*` views | — (base layout) | medium (257) |

## frontend/views/my-application/ (1 file)
| File | Purpose | Layout | Size |
|---|---|---|---|
| index.php | Logged-in candidate's application list (across tracks) | mainNavy | large (353) |

## frontend/views/online-payment/ (1 file)
| File | Purpose | Layout | Size |
|---|---|---|---|
| index.php | Unmodified Yii2 scaffold stub (`online-payment/index`) — dead/default action view | mainNavy | tiny (9) |

## frontend/views/sailor-candidate/ (9 files)
General Sailor Candidate application wizard — parallel to `de-sailor/` but without the diploma/trade education section.

| File | Purpose | Layout | Size |
|---|---|---|---|
| academic_info.php | Application wizard step — academic info | mainNavy | medium (201) |
| application_preview.php | Application preview before final submission | mainNavy | large (730) |
| application_verify_preview.php | Post-submission verification/preview page | mainNavy | large (615) |
| candidate/application_form_download.php | Download-application-form entry page | mainNavy | small (99) |
| candidate/application_form_pdf.php | Standalone HTML — application form rendered to PDF | — (standalone) | large (1005) |
| candidate/application_verification_pdf.php | Standalone HTML — verification slip rendered to PDF | — (standalone) | large (609) |
| candidate/my_application.php | Candidate's own application status page | mainNavy | medium (125) |
| payment.php | Application wizard step — payment | mainNavy | medium (243) |
| personal_info.php | Application wizard step — personal info (first step) | mainNavy | large (1025) |

## frontend/views/site/ (10 files)
Static/marketing pages and generic (non-candidate-scoped) auth flows.

| File | Purpose | Layout | Size |
|---|---|---|---|
| about.php | Static "About" page | mainNavy | tiny (16) |
| contact.php | Static "Contact" page | mainNavy | small (45) |
| error.php | Generic error page | mainNavy | small (27) |
| form_c.php | Standalone/unwired demo form ("Form", placeholder labels, `action="#"`) — looks like unused scaffolding | mainNavy | small (56) |
| index.php | Public homepage ("Life at Bangladesh Navy" landing content; title still default "My Yii Application") | mainNavy | medium (191) |
| login.php | Generic Yii2-scaffold login form | mainNavy | small (41) |
| requestPasswordResetToken.php | Generic forgot-password request form | mainNavy | small (31) |
| resendVerificationEmail.php | Resend account-verification email form | mainNavy | small (31) |
| resetPassword.php | Generic reset-password form | mainNavy | small (31) |
| signup.php | Generic Yii2-scaffold signup form | mainNavy | small (35) |

## frontend/components/views/ (2 files)
Partials rendered by view components/widgets, reused across the application wizards.

| File | Purpose | Size |
|---|---|---|
| step_and_support.php | Wizard step-progress indicator (Payment/Academic/etc., highlights active step) | medium (52) |
| support_no.php | Bengali-language support/contact-number text block (phone + email), shown on wizard steps | small (15) |

---

## backend/views/can-designation/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update candidate designation form partial | admin | small (67) |
| index.php | Candidate Designations list | admin | medium (100) |
| view.php | Candidate designation detail view | admin | small (44) |

## backend/views/de-sailor-report/ (6 files)
Reports scoped to the Direct-Entry Sailor track (parallel to `report/`).

| File | Purpose | Layout | Size |
|---|---|---|---|
| candidate_filter.php | Candidate Filter report (DE track) | admin | medium (202) |
| monitoring_application.php | Candidate Monitoring report (DE track) | admin | medium (114) |
| payment_report.php | Payment Report (DE track) | admin | medium (124) |
| pdf/candidate_filter_pdf.php | Standalone HTML — candidate filter results as PDF (identical to `report/pdf/candidate_filter_pdf.php`) | — (standalone) | small (88) |
| pdf/payment_report_pdf.php | Standalone HTML — payment report as PDF (identical to `report/pdf/payment_report_pdf.php`) | — (standalone) | small (50) |
| pdf/reference_candidate_pdf.php | Standalone HTML — reference-candidate list as PDF | — (standalone) | medium (170) |

## backend/views/de-sailors/ (8 files)
CRUD/review screens for Direct-Entry Sailor candidates plus a reference-candidate sub-flow.

| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Update DE candidate information form partial | admin | medium (180) |
| index.php | Direct Entry Sailors list | admin | large (269) |
| reference/02092025_reference_candidate.php | Dated backup/variant of `reference_candidate.php` (date-stamped filename `02092025`) | admin | medium (170) |
| reference/add_reference_candidate.php | Add reference candidate form | admin | small (96) |
| reference/reference_candidate.php | Reference Candidate list | admin | medium (196) |
| reference/update_reference_candidate.php | Update reference candidate form (byte-identical to `sailors/reference/update_reference_candidate.php`) | admin | medium (100) |
| update.php | Update DE Sailor page wrapper (renders `_form`) | admin | tiny (23) |
| view.php | DE Sailor candidate detail view | admin | medium (162) |

## backend/views/districts/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update district form partial | admin | small (53) |
| index.php | Districts list | admin | small (99) |
| view.php | District detail view | admin | small (44) |

## backend/views/eligibility/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update eligibility rule config form partial | admin | large (250) |
| index.php | Eligibilities list | admin | medium (180) |
| view.php | Eligibility rule detail view | admin | small (71) |

## backend/views/layouts/ (5 files)
| File | Purpose | Extends | Size |
|---|---|---|---|
| admin.php | Main admin panel layout shell (default admin layout) | — (base layout) | small (69) |
| blank.php | Chromeless layout — used by `SiteController` login action | — (base layout) | small (33) |
| left_side_menu.php | Admin sidebar navigation partial, included by `layouts/admin.php` | — (partial) | medium (183) |
| main.php | Unused leftover Yii2-scaffold default layout (`Nav::widget`/`Breadcrumbs::widget`) — no controller assigns it | — (base layout) | small (82) |
| top_bar.php | Admin top navbar partial, included by `layouts/admin.php` | — (partial) | small (68) |

## backend/views/log-report/ (1 file)
| File | Purpose | Layout | Size |
|---|---|---|---|
| report.php | Site Activity Log report (admin login/action audit trail) | admin | medium (237) |

## backend/views/report/ (15 files)
General reporting suite (parallel to `de-sailor-report/`, but for the general Sailor Candidate track / cross-track use).

| File | Purpose | Layout | Size |
|---|---|---|---|
| candidate_filter.php | Candidate Filter report | admin | large (291) |
| center_candidate.php | Exam-center-wise candidate report | admin | medium (167) |
| center_date_candidate.php | Exam center & exam date wise candidate report | admin | medium (162) |
| district_candidate.php | District-wise candidate report | admin | medium (158) |
| exam_date_check_by_center_designation.php | Candidate Monitoring — exam date check by center/designation | admin | medium (193) |
| json_for_ls.php | "Json for LS" — JSON data export/debug view for an LS (likely "List Selection"/external) integration | admin | medium (123) |
| monitoring_application.php | Candidate Monitoring report | admin | medium (114) |
| payment_report.php | Payment Report | admin | medium (124) |
| pdf/candidate_filter_pdf.php | Standalone HTML — candidate filter results as PDF | — (standalone) | small (88) |
| pdf/center_candidate_pdf.php | Standalone HTML — center-candidate report as PDF | — (standalone) | small (80) |
| pdf/district_candidate_pdf.php | Standalone HTML — district-candidate report as PDF | — (standalone) | small (79) |
| pdf/exam_date_center_candidate_pdf.php | Standalone HTML — exam-date/center candidate report as PDF | — (standalone) | small (78) |
| pdf/payment_report_pdf.php | Standalone HTML — payment report as PDF | — (standalone) | small (50) |
| pdf/reference_candidate_pdf.php | Standalone HTML — reference-candidate list as PDF | — (standalone) | medium (201) |
| same_academic_info.php | Report of candidates sharing the same academic info (duplicate-detection; title mislabeled "Center candidate") | admin | medium (144) |

## backend/views/sailor-batch-configuration/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update batch configuration rule form partial | admin | large (400) |
| index.php | Sailor Batch Configurations list | admin | medium (223) |
| view.php | Batch configuration detail view | admin | small (53) |

## backend/views/sailor-batchs/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update recruitment batch form partial | admin | medium (112) |
| index.php | Sailor Batchs list | admin | medium (101) |
| view.php | Batch detail view | admin | small (53) |

## backend/views/sailor-cent-dist-mapping/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update center-district mapping form partial | admin | small (61) |
| index.php | Center District Mappings list | admin | medium (118) |
| view.php | Mapping detail view | admin | small (43) |

## backend/views/sailor-centers/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update exam center form partial | admin | small (50) |
| index.php | Sailor Centers list | admin | small (88) |
| view.php | Exam center detail view | admin | small (44) |

## backend/views/sailors/ (6 files)
CRUD/review screens for the general Sailor Candidate track plus a reference-candidate sub-flow (parallel to `de-sailors/`).

| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Update candidate information form partial | admin | medium (272) |
| index.php | Sailors list | admin | large (472) |
| reference/add_reference_candidate.php | Add reference candidate form | admin | small (129) |
| reference/reference_candidate.php | Reference Candidate list | admin | large (257) |
| reference/update_reference_candidate.php | Update reference candidate form (byte-identical to `de-sailors/reference/update_reference_candidate.php`) | admin | medium (100) |
| view.php | Sailor candidate detail view | admin | medium (158) |

## backend/views/site/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| error.php | Admin error page | admin | small (27) |
| index.php | Admin dashboard — shows total completed applications with roll no. assigned | admin | medium (179) |
| login.php | Admin login form | blank | small (34) |

## backend/views/subjects/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update subject form partial | admin | small (64) |
| index.php | Subjects list | admin | small (94) |
| view.php | Subject detail view | admin | small (44) |

## backend/views/unions/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update union form partial | admin | small (55) |
| index.php | Unions list | admin | medium (120) |
| view.php | Union detail view | admin | small (46) |

## backend/views/upozilas/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Add/update upazila form partial | admin | small (56) |
| index.php | Upazilas list | admin | small (111) |
| view.php | Upazila detail view | admin | small (45) |

## backend/views/user/ (3 files)
| File | Purpose | Layout | Size |
|---|---|---|---|
| _form.php | Update admin user form partial | admin | small (60) |
| index.php | Users list | admin | medium (148) |
| view.php | Admin user detail view | admin | small (54) |

---

## Duplicate / dead / placeholder views flagged

| File | Issue |
|---|---|
| `frontend/views/de-sailor/index.php` | Unmodified Yii2 scaffold stub ("de-sailor/index" boilerplate text) — default action view, not a real page. |
| `frontend/views/online-payment/index.php` | Unmodified Yii2 scaffold stub ("online-payment/index" boilerplate text) — default action view, not a real page. |
| `frontend/views/site/form_c.php` | Static, unwired demo form (`action="#"`, generic "Name" labels repeated) — looks like leftover scaffolding, not linked into the real flow. |
| `frontend/views/site/index.php` | Title still the Yii2 default `'My Yii Application'` despite being the real public homepage — cosmetic bug. |
| `backend/views/layouts/main.php` | Unused leftover Yii2-scaffold default layout — no controller sets `layout = 'main'`; `admin` and `blank` are the only layouts actually assigned. |
| `backend/views/de-sailors/reference/02092025_reference_candidate.php` | Date-stamped filename (`02092025`) strongly suggests a dated backup/working copy of `reference_candidate.php`, not a routed view — check if any action still references it. |
| `backend/views/report/pdf/candidate_filter_pdf.php` ↔ `backend/views/de-sailor-report/pdf/candidate_filter_pdf.php` | Byte-identical files (0-line diff) duplicated across the `report/` vs `de-sailor-report/` split rather than shared. |
| `backend/views/report/pdf/payment_report_pdf.php` ↔ `backend/views/de-sailor-report/pdf/payment_report_pdf.php` | Byte-identical files (0-line diff), same duplication pattern. |
| `backend/views/sailors/reference/update_reference_candidate.php` ↔ `backend/views/de-sailors/reference/update_reference_candidate.php` | Byte-identical files (0-line diff), same duplication pattern. |

## Copy-paste title bugs spotted in passing (not part of the ask, noting for awareness)
`backend/views/report/same_academic_info.php` sets `$this->title = 'Center  candidate'` (copy-pasted from `center_candidate.php`) despite being the "same academic info" duplicate-detection report. `frontend/views/site/index.php` keeps the Yii2-default title `'My Yii Application'` on the live public homepage.
