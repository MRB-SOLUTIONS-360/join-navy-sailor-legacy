# Deep-Dive: Frontend Sailor / DE-Sailor Application Wizard

Scope: the two parallel multi-step candidate application flows under `/sailor-candidate/*` and `/de-sailor/*` — payment → academic-info → personal-info → application-preview → complete-application → download, plus the `verify-candidate` public verification page and (Sailor-track only) the `refund-phone`/`cancel-application` AJAX actions. Portal: **frontend** (candidate-facing). This is a Yii2 2.0 advanced-template app, not Laravel — there is no `routes/web.php`; routes are `controller-id/action-id` resolved by Yii's default `urlManager` (see `docs/00-inventories/route_inventory.md`), and there is no `FormRequest` layer — all field validation lives in the `Sailors`/`DeSailors` ActiveRecord models' `rules()`.

All file paths are relative to the repo root:
`/home/bs-01692/Personal/MRB/Join Navy/Legacy/join-navy-sailor-legacy`

Primary sources for this document: direct reads of `frontend/controllers/SailorCandidateController.php` (1195 lines) and `frontend/controllers/DeSailorController.php` (1118 lines) in full, `common/models/Sailors.php`/`common/models/DeSailors.php` `rules()`, `common/static/DataEncryption.php`, `common/static/StaticMethod.php`, `common/components/R2Storage.php`, and the views `frontend/views/{sailor-candidate,de-sailor}/{payment,academic_info}.php`. Cross-referenced against `docs/00-inventories/controller_inventory.md`, `model_inventory.md`, `service_inventory.md`, `middleware_inventory.md`, `route_inventory.md`, and `view_inventory.md`. Where a claim in an inventory file turned out to be imprecise once checked against source, that is flagged explicitly below (see §8).

Reference for structure/tone: `join-navy-officer-legacy/docs/04-deep-dives/deepdive_frontend_application_wizard.md` (the officer app's Laravel/Blade equivalent, gated by `CanContinueApplication`/`canApply` middleware). The sailor app has **no such middleware** — see §0.

---

## 0. How step-progression is gated — there is no middleware, only manual per-action checks

The officer app's `canApply` middleware (`CanContinueApplication::handle()`) centralizes phase-gating, closed-batch checks, and roll-number lock-out in one place, applied declaratively via `$this->middleware('canApply', ['only' => [...]])`. **This app has no equivalent.** Confirmed by `middleware_inventory.md` §3.6/§3.8 and by reading both controllers directly: neither `actionPayment`, `actionAcademicInfo`, `actionPersonalInfo`, `actionApplicationPreview`, nor `actionCompleteApplication` share a filter or trait. Instead, **every action re-implements its own phase check inline**, copy-pasted near-verbatim across both controllers:

```php
if ($sailor->phase != Constants::SAILOR_PHASE_ONE) {   // actionPayment
    Yii::$app->session->setFlash('application_close', "Please continue application from here");
    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
}
```
(`SailorCandidateController.php:81-84`, `DeSailorController.php:42-45` — identical except the redirect target, which is always `/my-application` in both, not a "continue from here" deep link like the officer app's `can-app-list`.)

The five phases (`common/static/Constants.php:100-104`) are far coarser than the officer app's seven:

| Constant | Value | Meaning | Checked by |
|---|---|---|---|
| `SAILOR_PHASE_ONE` | 1 | needs payment | `actionPayment` (both controllers) |
| `SAILOR_PHASE_TWO` | 2 | needs academic info | `actionAcademicInfo` (both controllers) |
| `SAILOR_PHASE_THREE` | 3 | needs personal info | `actionPersonalInfo` (both controllers — but only checked when the request is **not** POST, see below) |
| `SAILOR_PHASE_FOUR` | 4 | needs application preview / ready to submit | not explicitly gate-checked on `actionApplicationPreview`/`actionCompleteApplication` — see below |
| `SAILOR_PHASE_FIVE` | 5 | application finally submitted (roll/exam-date assigned) | terminal; `actionCompleteApplication` sets it, no action requires exactly this value to render |

Notable divergences from a clean "phase == required value or redirect" pattern:

1. **`actionPersonalInfo`'s phase check is conditioned on HTTP method**: `if ($sailor->phase != Constants::SAILOR_PHASE_THREE && Yii::$app->request->getMethod() != 'POST') { ...redirect... }` (`SailorCandidateController.php:413`, `DeSailorController.php:389`). Because of the `&&`, a POST request to this action is **never** phase-checked — only GET (page-load) requests are. A candidate who has already advanced past phase 3 could still re-POST the personal-info form and have it accepted, since the guard only fires on the render path.
2. **`actionApplicationPreview` and `actionCompleteApplication` have no phase check at all.** Both instead guard on a different signal — whether a roll number already exists:
   ```php
   if ($sailor->serial_no) {
       return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
   }
   ```
   (`actionApplicationPreview`: `SailorCandidateController.php:652`, `DeSailorController.php:673`; `actionCompleteApplication`: `SailorCandidateController.php:686`, `DeSailorController.php:713`.) This means a candidate who has only reached phase 2 (academic info) can navigate directly to `/sailor-candidate/application-preview/{slug}` and it will render — the "preview" step is not actually locked behind having completed personal info; it will simply show whatever fields happen to be populated (or blank) at that point. This is the sailor-app analogue of the officer app's side-effecting-GET note, but weaker: there is no re-persistence side effect here, just an absent gate.
3. **Every action additionally re-checks `SailorBatchs::isCandidateContinueApplication(batch_id, isPaid)` inline**, after the phase check, on every POST branch — this is the closed-batch check the officer app's middleware centralizes in step 5 of its `handle()`. Here it is called separately inside `actionPayment`, `actionAcademicInfo`, `actionPersonalInfo`, and `actionCompleteApplication` (4 separate call sites per controller, 8 total), each with its own copy of the flash message `"Sorry. Application is closed now."` and redirect to `/my-application`.
4. **Batch-live-mode / secret-key gate is entirely client-side**, same shape as the officer app's finding: `payment.php` embeds `stgPin` (the batch's plaintext `secrate_key`) and intercepts `#form_submit`'s click with a native `prompt()`; on mismatch it `return false`s from the click handler but does not call `preventDefault()`, so — as in the officer app — this is not a real access control, just a soft speed bump (`frontend/views/sailor-candidate/payment.php:32-47`, `frontend/views/de-sailor/payment.php:~14-35`, byte-identical logic in both).

### Access-control gaps (from `middleware_inventory.md`, confirmed against source)

Beyond phase-gating, there is no consistent "must be logged in" filter either:

- **`SailorCandidateController`** declares `AccessControl` (`behaviors()`, lines 39-59) scoped via `'only' => ['payment', 'academic-info']` — only those two of its ten actions are actually filtered. The `verify-candidate` rule inside the same block (`'allow' => true, 'roles' => ['?']`) is dead configuration: because `verify-candidate` isn't in `only`, the filter never runs for it at all, so it ends up unrestricted-by-default anyway (which happens to be the intended behavior for a public verification page, but not via the mechanism that looks like it's providing it). The other eight actions — `personal-info`, `application-preview`, `complete-application`, `download`, `download-form`, `refund-phone`, `cancel-application` — have **no `AccessControl` and no manual `isGuest` guard**; they dereference `Yii::$app->user->identity->...` directly and would fatal on a null identity for a guest request rather than redirect cleanly.
- **`DeSailorController` has zero `behaviors()` override and zero `isGuest` checks anywhere** — every one of its eight actions is reachable by a guest request at the routing layer (it would still typically fatal on the first `Yii::$app->user->identity->id` dereference inside `findModel()`, but that's an uncaught-error failure mode, not a deliberate gate). This is a strictly weaker posture than its `SailorCandidateController` sibling, which at least partially covers `payment`/`academic-info`.

---

## 1. Page Inventory

Both controllers expose the same eight-step action set under parallel route prefixes (`sailor-candidate/*` vs `de-sailor/*`, per `route_inventory.md`). Documented once per step, with DeSailor-specific deltas called out inline; the full field-by-field Sailor-vs-DeSailor diff is in §8.

### 1.1 Payment (phase 1 → 2)

- **Page Name**: Payment Type & Birth Registration
- **URL**: `/sailor-candidate/payment/{slug}` (`/de-sailor/payment/{slug}` for the DE track)
- **Route**: `sailor-candidate/payment` → `actionPayment($slug=null)` (`SailorCandidateController.php:76`) / `de-sailor/payment` → `actionPayment($slug=null)` (`DeSailorController.php:36`)
- **Portal**: frontend, candidate-only. `SailorCandidateController` gates this action via `AccessControl` (`roles=>['@']`); `DeSailorController` has no gate on it at all.
- **View File**: `frontend/views/sailor-candidate/payment.php` (243 lines) / `frontend/views/de-sailor/payment.php` (144 lines)
- **Purpose**: Step 1 — capture `birth_registration_no` and `payment_type`/`agree_payment_terms`, then hand off to the SSLCommerz gateway (or short-circuit straight past payment if a prior transaction is found to have actually succeeded).
- **Detailed Description**: `actionPayment()` first hard-redirects to `/my-application` if `phase != SAILOR_PHASE_ONE`. Sets `$sailor->scenario = Sailors::PAYMENT` (the model-validation scenario, Yii2's `FormRequest` equivalent), pre-fills `birth_registration_no` from `Yii::$app->user->identity->birth_registration_no`, and resolves `$batch_setting_info = SailorBatchs::batchById(...)` to pick `payment_mode` (`live` vs `sandbox`, from `batch_setting_info['payment_mode']`). Supports AJAX field-level validation (`\yii\widgets\ActiveForm::validate($sailor)`, the Yii2 idiom for what the officer app does with a JSON 400 response from `validation.js`).
  - **Payment-retry reconciliation** (before generating a fresh transaction): if `payment_status == PAYMENT_UNPAID` and a `validation_id` already exists, it collects every previously-attempted transaction id (`all_requested_tran_id`, a JSON array column, falling back to the single `validation_id` if that column is empty) and calls `SSLPayment::allRequestListByTranIds(app_type, payment_mode, tranIds)`. If SSLCommerz reports any of them as actually `PAYMENT_VALIDATED`, the candidate is marked paid in place (`card_type`, `trans_date`, `ref_id`, `card_no`, `payment_api` snapshot, `payment_status = PAYMENT_PAID`, `phase = SAILOR_PHASE_TWO`, `amount`, `store_amount = SSLPayment::STORE_AMOUNT`) and the flow redirects straight to `academic-info`, **skipping the gateway entirely** — same shape as the officer app's retry path.
  - Otherwise it builds a fresh `payment_info` array (`tran_id` — `date('Ymdhis') . rand(111,999) . id` for Sailor, `date('ymdhis') . rand(111111,999999)` for DeSailor, a formatting inconsistency between the two tracks; `amount` from `batch_setting_info['payment_amount']`; `cus_name`/`cus_email`/`cus_phone` from `Yii::$app->user->identity`; `cus_add1`/`cus_city`/`cus_state`/`cus_country` from the linked `CanEligibilityCheckInfo` row, falling back to `'Dhaka'`/`'BD'`; `opt_a => 'sailor'` or `'de_sailor'`; `opt_b => 'r_{id}#u_{user_id}'`, the same correlation-id convention the officer app uses; `opt_d => app_unique_id`), session-stores it under `payment_info`, sets `dob`/`nationality` on the model from the eligibility-check row, saves, and redirects to `online-payment/payment-ssl` (`OnlinePaymentController::actionPaymentSsl()`, which reads `session('payment_info')` and calls `SSLPayment::requestInit()`).
- **User Actions Available**: `birth_registration_no` text field, `payment_type` radio list (`StaticMethod::paymentType()` — options rendered but effectively a single "Online" choice, same as the officer app), `agree_payment_terms` single-option radio (value `1`), links to Terms & Condition / Refund & Return Policy / Privacy Policy PDFs, submit `#form_submit` "Continue".
- **Sailor-only extra UI**: if `batch_setting_info['allow_refund'] == Constants::YES`, clicking `#form_submit` is intercepted (`e.preventDefault()`) to open a Bootstrap `Modal` (`#confirmModal`) asking for a `refund_phone` number before the real form submits — see §1.9. **This modal does not exist in the DeSailor payment view at all** (confirmed: `grep -n "Modal\|refund" frontend/views/de-sailor/payment.php` matches only the unrelated "Refund and Return Policy" PDF link text) — DE-track candidates are never asked for a refund contact number at payment time.
- **Blade/View partials**: `frontend\components\StepAndSupport::widget(['steps'=>[1], 'slug'=>...])`, `frontend\components\SupportNo::widget([...])` — the Yii2-widget equivalent of the officer app's `support_steps.*` Blade partials.
- **AJAX**: field-level Yii `ActiveForm` client validation (POSTs to the same URL with `Yii::$app->request->isAjax` true); the Sailor-track refund modal posts `POST /sailor-candidate/refund-phone` on `#modalSubmit` click, then submits the real form on success (see §1.9).
- **Modal**: the refund-phone confirmation modal (Sailor track only, conditional on `allow_refund`) — a genuine Bootstrap 5 `Modal` widget, unlike the officer app's native-`prompt()`-only dialogs. See §1.9 for full detail.
- **Debug scaffolding still live**: on validation failure, the `else` branch does `echo '<pre>'; print_r($sailor->getErrors()); echo '</pre>'; die();` (`SailorCandidateController.php:169-173`, `DeSailorController.php:132-135`) — a raw PHP error dump terminating the request, present in every wizard step's POST-failure branch in both controllers.

### 1.2 Academic Info (phase 2 → 3)

- **URL / Route**: `sailor-candidate/academic-info/{slug}` → `actionAcademicInfo($slug=null)` (`SailorCandidateController.php:199`) / `de-sailor/academic-info/{slug}` → `actionAcademicInfo($slug=null)` (`DeSailorController.php:158`)
- **Portal**: frontend, candidate-only for Sailor (AccessControl-gated); ungated for DeSailor.
- **View**: `frontend/views/sailor-candidate/academic_info.php` (201 lines) / `frontend/views/de-sailor/academic_info.php` (275 lines, +74 for the diploma/trade block)
- **Purpose**: Step 2 — JSC (optional), SSC, HSC academic history; for DE-track, plus a Diploma/Trade-Course block.
- **Detailed Description**: Loads `CanEligibilityCheckInfo` to pre-seed `ssc_group`/`hsc_dip_group` from the original eligibility-check answers. Sets scenario based on designation (`ACADEMIC_INFO_JSC` if `candidate_designation == TOPASS_PRIMARY_KEY`, else `ACADEMIC_INFO_JSC_SSC`, for Sailor; `ACADEMIC_DE_SAILOR_ARTIFICER` vs `ACADEMIC_DE_SAILOR_DOCKYARD` for DeSailor, keyed off `candidate_type`). On POST, this is the step that hits the **external teletalk SSC/HSC result API** — see §6 — whose response both auto-fills academic fields and cross-checks eligibility (GPA floor, allowed academic group) inline in the controller, not via a client-side "Check Result" button + separate AJAX endpoint the way the officer app does it. There is no `ssc-result-check`/`hsc-result-check` AJAX action anywhere in this app's `AjaxController.php` (confirmed: `docs/00-inventories/controller_inventory.md`'s `AjaxController` action table lists only `actionDistrictByCandidateType`, `actionHscDiplomaAcGroup`, `actionUpazialByDistrict`, `actionUnionByUpazial` — no result-check endpoints) — the teletalk call happens synchronously inside the form-submit handler itself, server-side only.
  - **DE-track has an opt-out**: `DeSailors` carries `public $skipTeleTalkValidation = true` (`DeSailors.php`, per `model_inventory.md` §4) as a default. When true (the default), `actionAcademicInfo` skips the external API call entirely and instead lets the candidate self-report `ssc_gpa`/`ssc_group`/`hsc_dip_gpa`/`hsc_dip_group` as free-input fields (`DeSailorController.php:212,300-330`) — the view conditionally renders two extra manual-entry columns ("প্রাপ্ত জিপিএ"/"বিভাগ") when `$skipTeleTalkValidation` is true (`frontend/views/de-sailor/academic_info.php:136-141,165-176,209-220`). **The Sailor-track view has no such branch at all** — `academic_info.php` for `sailor-candidate` always shows the teletalk-only column layout (no GPA/group manual-entry cells), confirming the teletalk-skip mechanism is DE-track-exclusive.
  - **SSC eligibility cross-check** (both tracks, when teletalk is used): resolves `Eligibility::eligibilityBySession($candidate_designation)`, and — unless `ssc_edu_board == Constants::EDU_BOARD_TEC` (technical board, exempted) — rejects with a Bangla error (`academic_info_already_used_in_ssc`) if the teletalk-returned `gpa` is below `eligible_config['ssc_result']`, or if the returned `studGroup` isn't in the comma-list `eligible_config['ssc_ac_group']`. For Sailor track only, there's an additional TEC-board branch that still enforces the GPA floor when `candidate_designation == 4` (Medical) even on a technical board (`SailorCandidateController.php:301-309`) — **DeSailor's academic-info action has no equivalent TEC/Medical-designation carve-out**, confirmed absent from `DeSailorController.php`'s SSC-check block.
  - Age is (re)computed from `dob` (set from the teletalk `dob` field if present) against `batch_setting_info['circular_date']` via `StaticMethod::getDifferenceBetweenTwoDateYearMonth()`, same helper both tracks use.
  - On success, `phase = SAILOR_PHASE_THREE`, saves, redirects to `personal-info`.
- **User Actions Available**: JSC institute/reg-no/passing-year/GPA (all optional text inputs, no validator enforces `required` on these — `jscAcademicInfoValidate`/`jscSscAcademicInfoValidate` only fire when the record's `scenario` is explicitly `ACADEMIC_INFO_JSC`/`ACADEMIC_INFO_JSC_SSC`, i.e. only for the Sailor track's non-TOPASS designations), SSC education-board select (`StaticMethod::educationBoard()`), roll no, reg no, passing year, HSC/Diploma board select + roll/reg/passing-year; DE-track adds Diploma/Trade institute name, course select (`$courses_list`, sourced from `Eligibility::eligibilityBySession()['hons_diploma_subject']` for Artificer or `['trade_course_subject']` for Dockyard, resolved to `Subjects::getAllActiveSubjectByIdIn()`), registration/roll, and GPA.
- **AJAX**: none page-specific — this step, like the officer app's ISSB-info step, is a plain full-page POST. The `skipTeleTalkValidation` manual-entry fields (DE-track) are plain form inputs, not fetched via AJAX.
- **Modal**: none.

### 1.3 Personal Info (phase 3 → 4)

- **URL / Route**: `sailor-candidate/personal-info/{slug}` → `actionPersonalInfo($slug=null)` (`SailorCandidateController.php:406`) / `de-sailor/personal-info/{slug}` → `actionPersonalInfo($slug=null)` (`DeSailorController.php:377`)
- **Portal**: frontend, no `AccessControl` on either controller for this action — relies purely on `Yii::$app->user->identity` being non-null (see §0's access-control gaps).
- **View**: `frontend/views/sailor-candidate/personal_info.php` (1025 lines) / `frontend/views/de-sailor/personal_info.php` (1074 lines)
- **Purpose**: Step 3 — the largest step: full personal/contact/address/parents/guardian/naval-child/Ansar-VDP/small-ethnic-group data, plus photo upload to Cloudflare R2.
- **Detailed Description**: Sets `$sailor->scenario` to `PERSONAL_INFO` or `PERSONAL_INFO_WITH_IMAGE` depending on whether a photo already exists on disk (`!empty($prevImage) || !file_exists(...)` — note the `||` here means the *with-image* (photo-required) scenario is actually selected whenever a previous image string is non-empty OR the file is missing on disk; this reads as an inverted/buggy condition relative to its evident intent of "require photo only if none exists yet", but it is what both controllers do, byte-identical). On POST: creates a per-candidate upload directory (`@rootDirFilUpload/media/{sailor_candidate|de_sailor_candidate}/{id}`), re-checks the batch-open status, then handles the file upload (§4), re-encrypts PII fields (§5), recomputes age, sets `phase = SAILOR_PHASE_FOUR`, saves, and pushes `dob` back onto the `User` record if it changed (`if (Yii::$app->user->identity->dob !== $sailor->dob) { ... $user->save(); }`). Redirects to `application-preview`.
  - **DeSailor's version has one behavioral quirk Sailor's doesn't**: after the successful save/redirect block, there's a stray `if ($fileImage) $fileImage->saveAs($path);` (`DeSailorController.php:547`) — a **second** `saveAs()` call on the same `UploadedFile` instance, after it was already saved once inside the upload try/block above (`:478`) and after the local temp file was already `unlink()`'d (`:489`). Since Yii2's `UploadedFile::saveAs()` moves the file from PHP's `tmp_name`, calling it twice on the same request is normally a no-op-or-warning (the underlying temp file is already consumed by the first call) rather than a functional duplicate-write — flagged here as leftover/confused code, not a working feature. `SailorCandidateController`'s equivalent branch has no such second call.
  - **GET-path enrichment** (both tracks): resolves district dropdowns via `Districts::getAllActiveDistrictBySlug($sailor->eligible_district)`, unsets the two synthetic "Navy Child" / "Nou Scout" district slugs from the general district list (special quota categories handled elsewhere, not as literal geographic districts), widens the *permanent*-address district dropdown to all districts if the candidate's eligible district is one of those two synthetic slugs, resolves `exam_center_name` for display, and — if the linked `CanEligibilityCheckInfo.candidate_type == ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA` ("child of naval officer" quota) — pre-fills `is_child_of_naval_officer`, `naval_office_no`, `naval_rank` from that eligibility record. DE-track additionally resolves the same `$courses_list` dropdown as the academic-info step (needed again here because it's re-rendered somewhere on this large page, per `view_inventory.md`'s size note).
- **User Actions Available** (full field list, confirmed by grepping every `$form->field($model, '...')` call in `sailor-candidate/personal_info.php`):
  - Identity: `name`, `name_bangla`, `dob` (date), `age_according_to_circular` (read-only/computed), `religion`, `marital_status`, `nationality`, `gender`, `district` (eligible district, display).
  - Address (present/current): `current_district`, `current_thana`, `current_union`, `current_post_office`, `current_village`, `current_word_no`, `current_post_code`, `current_phone`.
  - Address (permanent): `permanent_district`, `permanent_thana`, `permanent_union`, `permanent_post_office`, `permanent_village`, `permanent_word_no`, `permanent_post_code`, `permanent_phone`.
  - Parents: `father_name`, `father_name_bangla`, `father_phone`, `father_nid`, `father_occupation`; `mother_name`, `mother_name_bangla`, `mother_phone`, `mother_occupation`.
  - Guardian: `guardian_name`, `guardian_phone`, `guardian_relation`, `guardian_occupation`, `guardian_address` (all optional — no `required` rule targets any `guardian_*` field in either model).
  - Academic re-display: `jsc_institute_name`/`jsc_reg_no`/`jsc_passing_year`/`jsc_gpa`, `ssc_institute`/`ssc_group`/`ssc_edu_board`/`ssc_reg_no`/`ssc_roll_no`/`ssc_passing_year`/`ssc_additional_subject`/`ssc_gpa`, `hsc_dip_institute`/`hsc_dip_group`/`hsc_dip_board`/`hsc_dip_reg_no`/`hsc_dip_roll_no`/`hsc_dip_passing_year`/`hsc_dip_additional_subject`/`hsc_dip_gpa` — these fields are **editable again on this page**, not read-only, letting the candidate correct the institute name / additional-subject fields the teletalk API doesn't supply.
  - Special-category blocks: `experience_one_institute`/`_subject`/`_year`/`_cert_name` and `experience_two_institute`/`_subject`/`_year`/`_cert_name` (prior training/certification — see the correction note in §8: **this is not DE-track-exclusive**, `Sailors.php` carries the identical `experience_one`/`experience_two`/`experience_three`/`experience_four` field family and validates the first two pairs via the same `onlyInputEnglishCharacterValidation` rule DeSailors uses); `is_freedom_fighter` + conditional `freedom_fighter_relation`; `is_child_of_naval_officer` + conditional `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank`; `is_anser_vdp` + conditional `anser_vdp_rank`/`anser_vdp_office_no`; `is_khudro_jati_gosti` (small/indigenous ethnic-group quota flag, no further conditional sub-fields).
  - `photo` file input (conditionally required, see §4).
- **Blade/View partials**: same `StepAndSupport`/`SupportNo` widget pair as every step, `steps=>[1,2,3]`.
- **AJAX**: cascading-dropdown lookups for district→thana(upazila)→union, mirroring the officer app's pattern but via this app's own `AjaxController` rather than a cross-portal admin controller: `POST district-by-candidate-type` (`AjaxController::actionDistrictByCandidateType`), `GET upazial-by-district` (`actionUpazialByDistrict`), `GET union-by-upazial` (`actionUnionByUpazial`) — all confirmed public/unauthenticated (`AjaxController.php` has no `behaviors()`, only `beforeAction()` disabling CSRF; `middleware_inventory.md` §3.13 confirms no app-level backstop on the frontend, unlike the backend's equivalent controller).
- **Modal**: none.

### 1.4 Application Preview (phase 4, read-only)

- **URL / Route**: `sailor-candidate/application-preview/{slug}` → `actionApplicationPreview($slug=null)` (`SailorCandidateController.php:647`) / `de-sailor/application-preview/{slug}` → `actionApplicationPreview($slug=null)` (`DeSailorController.php:667`)
- **View**: `frontend/views/sailor-candidate/application_preview.php` (730 lines) / `frontend/views/de-sailor/application_preview.php` (741 lines)
- **Purpose**: Step 4 — read-only review of every field entered in steps 1–3, no form submission here beyond a "Continue" that routes into `complete-application`.
- **Detailed Description**: Guards on `if ($sailor->serial_no) { redirect to my-application }` (roll already assigned → nothing left to preview). Decrypts PII for display (§5), resolves `current_thana`/`permanent_thana` id → name via `Upozilas::upazilaNameById()`. DE-track additionally resolves `diploma_trade_course` (a subject id) to its display name via `Subjects::subjectFindById()` and passes it to the view as `$diploma_trade_course`. **No phase check at all** (see §0, point 2) — reachable from any phase once a `Sailors`/`DeSailors` row exists and has no roll number yet.
- **User Actions Available**: none editable — a single "Continue" button that POSTs to `complete-application`.
- **Modal**: none.

### 1.5 Complete Application (phase 4 → 5, roll/exam-date allocation)

- **URL / Route**: `sailor-candidate/complete-application/{slug}` → `actionCompleteApplication($slug=null)` (`SailorCandidateController.php:681`) / `de-sailor/complete-application/{slug}` → `actionCompleteApplication($slug=null)` (`DeSailorController.php:708`)
- **Purpose**: Final submission — allocates roll number and exam date/group, writes an immutable candidate-log snapshot to R2, and redirects to the download/congratulations screen. **POST-only**: `throw new BadRequestHttpException('This action only accepts POST requests.')` if not (`SailorCandidateController.php:919`, `DeSailorController.php:941`).
- Full mechanics documented separately in §7 (this is the most complex logic in the wizard, on par with the officer app's `ProcessRollGeneration` job — except here it runs **synchronously inline in the HTTP request**, not as a queued job; `service_inventory.md` confirms this app has no queue/job layer at all).

### 1.6 Download / Congratulations

- **URL / Route**: `sailor-candidate/download/{slug}` → `actionDownload($slug=null)` (`SailorCandidateController.php:927`) / `de-sailor/download/{slug}` → `actionDownload($slug=null)` (`DeSailorController.php:951`)
- **Purpose**: Generates the final Commission/Application-Form PDF via mPDF and streams it inline, then `exit()`s (no page chrome — this is a raw PDF response, not a Blade-rendered "Congratulations" screen with download buttons the way the officer app's `application_download.blade.php` is).
- **Detailed Description**: Decrypts PII, resolves `current_thana`/`permanent_thana` names, builds an `\Mpdf\Mpdf` instance (`curlAllowUnsafeSslRequests = true`, `debug = true` in both — debug mode left enabled in what reads as production PDF-generation code), renders `renderPartial('candidate/application_form_pdf', [...], true, false)` as the HTML source, and outputs inline (`'I'` mode) as `{name}({serial_no}).pdf`.
- **A separate, near-identical `actionDownloadForm($slug=null)`** exists on both controllers with **no ownership check** — it looks the model up directly by decrypted slug (`Sailors::find()->where(['id'=>...])->one()`, no `andWhere(['created_by'=>...])`), unlike `findModel()`'s owner-scoped lookup used by every other action. `SailorCandidateController::actionDownloadForm()` additionally prepends `+88` to `current_phone`/`permanent_phone` before rendering (`SailorCandidateController.php:990-992`) — **DeSailor's `actionDownloadForm()` has no equivalent phone-prefixing step.**

### 1.7 Verify Candidate (public)

- **URL / Route**: `sailor-candidate/verify-candidate/{slug}` → `actionVerifyCandidate($slug=null)` (`SailorCandidateController.php:1021`) / `de-sailor/verify-candidate/{slug}` → `actionVerifyCandidate($slug=null)` (`DeSailorController.php:1045`)
- **Purpose**: Public (no ownership check, same unscoped lookup pattern as `actionDownloadForm`) verification page rendering `application_verify_preview`.
- **Confirmed dead code**: both methods `return $this->render('application_verify_preview', [...])` and then have **unreachable code after the `return`** — a second, complete mPDF-generation block (verification-slip PDF via `renderPartial('candidate/application_verification_pdf', ...)`) that can never execute:
  - `SailorCandidateController.php:1051-1067` (17 lines after the `return` at line 1043)
  - `DeSailorController.php:1075-1092` (18 lines after the `return` at line 1067)

  Both blocks are structurally identical (build `\Mpdf\Mpdf`, `curlAllowUnsafeSslRequests = true`, `WriteHTML(...application_verification_pdf...)`, `Output(...'I')`, `exit()`) and both reference `candidate/application_verification_pdf.php` views that **do exist on disk** (confirmed by `controller_inventory.md`: "the verification-PDF one is dead code so moot despite existing") but are unreachable — this reads as an earlier design where `verify-candidate` streamed a PDF directly, later changed to render an HTML preview page instead, with the old PDF branch left in place below the new `return` rather than deleted. Same finding, same shape, in both controllers — copy-paste-propagated dead code, not independently introduced twice.

### 1.8 Refund Phone (AJAX, Sailor-track only)

- **Route**: `sailor-candidate/refund-phone` → `actionRefundPhone()` (`SailorCandidateController.php:1090`). **No equivalent action exists on `DeSailorController`.**
- **Purpose**: Called from the payment-page refund modal (§1.9) — persists a `refund_phone` number (encrypted) on the `Sailors` row before the real payment form submits, only when the batch has `allow_refund == YES`.
- **Detailed Description**: Requires `Yii::$app->request->isAjax && $userId` (still an implicit-only guard, not `AccessControl`). Validates the posted `Sailors[refund_phone]`/`Sailors[id]` manually (not via the model's own `rules()` — this is hand-rolled validation inside the action): non-empty `refund_phone`, non-empty `id`, and a Bangladesh mobile-number regex `^(?:\+88|88)?(01[3-9]\d{8})$`, with Bangla error strings. On pass, re-loads the `Sailors` row **scoped to `created_by == $userId`** (this action, unusually among the ungated ones, does enforce ownership), encrypts the phone via `DataEncryption::dataEncrypt()`, and `save(false)`s (validation skipped — the manual checks above are the only validation that runs). Returns raw `\yii\helpers\Json::encode(['success'=>..., ...])`.

### 1.9 Cancel Application (AJAX, Sailor-track only)

- **Route**: `sailor-candidate/cancel-application` → `actionCancelApplication()` (`SailorCandidateController.php:1142`). **No equivalent action exists on `DeSailorController`** — DE-track candidates have no self-service cancellation path in this codebase at all; cancellation for that track, if it exists, would have to be an admin-side operation (out of scope for this document — see `backend/controllers/DeSailorsController.php` in `controller_inventory.md`, which likewise has no cancel action).
- **Detailed Description**: Reads the request body as raw JSON (`json_decode(Yii::$app->request->getRawBody(), true)`, not the usual `Yii::$app->request->post()` — meaning this endpoint expects `Content-Type: application/json`, not a form-encoded POST, unusual relative to every other action in this pair of controllers). Requires non-empty `slug` and `reason`. Loads the `Sailors` row scoped to `created_by == $userId` (ownership-enforced, same as `actionRefundPhone`). Sets `request_for_cancel = 1` and `reason = $post['reason']`, `save(false)`s. This flag is what surfaces the "Canceled" status on the backend's `SailorsController::actionCancelRequest()` list (per `controller_inventory.md`) — cancellation here is a **request flag for admin review**, not a self-service delete/withdraw; nothing in either frontend controller actually deletes the row or blocks further wizard progress based on `request_for_cancel`.

### The refund-phone confirmation modal, in full

Referenced from §1.1. This is the **only genuine modal dialog** in the sailor wizard (as opposed to the officer app's finding of zero modals and two native `prompt()`s — this app has both: the secret-key gate is still a `prompt()`, per §0 point 4, but the refund-phone flow is a real Bootstrap 5 `Modal` widget).

- **Trigger**: click on `#form_submit` on the Sailor-track `payment.php`, but **only** when `batch_setting_info['allow_refund'] == Constants::YES`; the click handler calls `e.preventDefault()` unconditionally in that branch, so the real form never submits from this click directly.
- **Fields**: `Sailors[refund_phone]` text input (label suppressed, `errorOptions=>['id'=>'refund-phone-error']` so the AJAX error handler can target it directly), plus a hidden `Sailors[id]`.
- **Validation**: none client-side beyond `type="text"`; server-side via `actionRefundPhone()` (§1.8).
- **Buttons**: modal footer "Cancel" (`data-dismiss=modal`) and "Confirm" (`#modalSubmit`).
- **Backend call**: `POST /sailor-candidate/refund-phone`, form-serialized (not raw JSON, unlike `cancel-application`).
- **Success flow**: hides the modal (`$('#confirmModal').modal('hide')`), then **programmatically submits the real payment form** (`$('#sailor-can-payment').submit()`) — i.e. the refund-phone save is a gate the real submission passes through, not a parallel/independent action.
- **Fail flow**: writes the server's Bangla error message into `#refund-phone-error` and shows `.invalid-feedback`; the modal stays open, the real form is never submitted.

---

## 2. Frontend File Mapping

| Layer | File |
|---|---|
| Layout | `frontend/views/layouts/mainNavy.php` |
| Views — Sailor track | `frontend/views/sailor-candidate/{payment,academic_info,personal_info,application_preview,application_verify_preview,candidate/application_form_download,candidate/application_form_pdf,candidate/application_verification_pdf (dead),candidate/my_application}.php` |
| Views — DE-Sailor track | `frontend/views/de-sailor/{payment,academic_info,personal_info,application_preview,application_verify_preview,candidate/application_form_download,candidate/application_form_pdf,candidate/application_verification_pdf (dead),candidate/my_application,index (dead scaffold stub)}.php` |
| Shared partials/widgets | `frontend/components/StepAndSupport.php` (+ `frontend/components/views/step_and_support.php`), `frontend/components/SupportNo.php` (+ `frontend/components/views/support_no.php`) |
| Controllers | `frontend/controllers/SailorCandidateController.php` (1195 lines), `frontend/controllers/DeSailorController.php` (1118 lines), `frontend/controllers/OnlinePaymentController.php` (gateway redirect + SSL callbacks), `frontend/controllers/AjaxController.php` (district/upazila/union cascades) |
| "Middleware" equivalent | **none** — see §0; gating is manual per-action `if ($model->phase != ...)` checks, no shared filter class |
| Validation | `common/models/Sailors.php` `rules()` (~460 lines of scenario-gated rules + ~15 custom inline validators), `common/models/DeSailors.php` `rules()` (structural twin, DE-specific trade fields added) — the Yii2 analogue of the officer app's per-step `FormRequest` classes, but unified into one model per track instead of one class per step |
| PII encryption | `common/static/DataEncryption.php` (AES via `Yii::$app->security->encryptByKey`/`decryptByKey`, prefix-tagged `ENC:`) |
| File storage | `common/components/R2Storage.php` (Cloudflare R2, Yii app component `Yii::$app->r2Storage`) |
| External result API | `common\static\StaticMethod::educationBoardResult()` (teletalk SSC/HSC lookup, `common/static/StaticMethod.php:531`) |
| Roll allocation | `Sailors::nextRollByBatchId()` / `DeSailors::nextRollByBatchId()`, `SailorBatchConfiguration::configurationByBatchCenterGenderCanDesigDistrictSlugAll()`, `SailorBatchConfigurationExamDate::{getListByConfigurationId,getNextKeyValue,getNextAvailableExamDate}()` |
| Models touched | `Sailors`, `DeSailors`, `SailorBatchs`, `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate`, `SailorCenters`, `CanEligibilityCheckInfo`, `Eligibility`, `Subjects`, `Districts`, `Upozilas`, `Unions`, `User` |
| Excluded/inactive variants | `frontend/controllers/OnlinePaymentController_shurjo_pay.php` (PSR-4 name-collision dead file, per `controller_inventory.md`) — not part of the wizard itself but shares the payment step's `online-payment/*` route namespace |

---

## 3. Form Documentation

### 3.1 Payment form

- **Form Name**: Payment Type & Birth Registration (`Sailors`/`DeSailors`, scenario `PAYMENT`)
- **Controller@Method**: `SailorCandidateController::actionPayment` / `DeSailorController::actionPayment`
- **Validation** (`rules()`, both models, scenario-gated):
```php
[['payment_type', 'agree_payment_terms'], 'required', 'on' => self::PAYMENT],
[['birth_registration_no'], 'birthRegistrationNoValidation', 'on' => self::PAYMENT, 'skipOnError' => false, 'skipOnEmpty' => false],
// Sailor track only:
['payment_type', 'paymentTypeValidate', 'on' => self::PAYMENT, 'skipOnError' => false, 'skipOnEmpty' => false],
```
`Sailors` has an extra `paymentTypeValidate` custom validator on top of the plain `required` that `DeSailors` doesn't declare — a small asymmetry; both still ultimately require a non-empty `payment_type`.

| Field | Required | Datasource | Notes |
|---|---|---|---|
| `birth_registration_no` | required (scenario `PAYMENT`), custom `birthRegistrationNoValidation` | free text | pre-filled from `Yii::$app->user->identity->birth_registration_no` |
| `payment_type` | required | `StaticMethod::paymentType()` | effectively one option, "Online" |
| `agree_payment_terms` | required | static, single option value `1` | terms-agreement checkbox rendered as a one-option radio list |

### 3.2 Academic Info form

- **Controller@Method**: `actionAcademicInfo` (both), scenarios `ACADEMIC_INFO_JSC`/`ACADEMIC_INFO_JSC_SSC` (Sailor) or `ACADEMIC_DE_SAILOR_ARTIFICER`/`ACADEMIC_DE_SAILOR_DOCKYARD` (DeSailor).
- **Sailor `rules()` excerpt** (`Sailors.php`):
```php
[['jsc_institute_name', 'jsc_reg_no', 'jsc_passing_year', 'jsc_gpa'], 'jscAcademicInfoValidate', 'on' => self::ACADEMIC_INFO_JSC, 'skipOnError' => false, 'skipOnEmpty' => false],
[['jsc_institute_name', 'jsc_reg_no', 'jsc_passing_year', 'jsc_gpa', 'ssc_edu_board', 'ssc_roll_no', 'ssc_reg_no', 'ssc_passing_year'], 'jscSscAcademicInfoValidate', 'on' => self::ACADEMIC_INFO_JSC_SSC', 'skipOnError' => false, 'skipOnEmpty' => false],
```
- **DeSailor `rules()` excerpt** (`DeSailors.php`):
```php
[['jsc_institute_name','jsc_reg_no','jsc_passing_year','jsc_gpa','ssc_edu_board','ssc_roll_no','ssc_reg_no','ssc_passing_year','diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa'], 'sailorArtificerValidation', 'on' => self::ACADEMIC_DE_SAILOR_ARTIFICER, 'skipOnError' => false, 'skipOnEmpty' => false],
// identical field list, 'sailorArtificerValidation' again (not a distinct 'dockyard' validator despite the different scenario name), 'on' => self::ACADEMIC_DE_SAILOR_DOCKYARD
```
Note: DeSailor's Dockyard scenario reuses the same `sailorArtificerValidation` method name as the Artificer scenario — either a genuinely shared validator (same rules for both DE sub-tracks) or a copy-paste that never got renamed; either way, the two DE sub-tracks validate identically at the model layer, differing only in which `$courses_list` the view populates (`hons_diploma_subject` vs `trade_course_subject` from `Eligibility`).

| Field | Sailor | DeSailor | Datasource |
|---|---|---|---|
| `jsc_institute_name`/`jsc_reg_no`/`jsc_passing_year`/`jsc_gpa` | present, required only under `ACADEMIC_INFO_JSC`/`_JSC_SSC` scenarios | present, required under both DE scenarios | free text |
| `ssc_edu_board` | present (custom validator + eligibility cross-check, §1.2) | present | `StaticMethod::educationBoard()` |
| `ssc_roll_no`/`ssc_reg_no`/`ssc_passing_year` | present | present | free text/numeric |
| `ssc_gpa`/`ssc_group` | populated from teletalk response only (no manual-entry UI) | populated from teletalk, **or** manual free-input/select when `skipTeleTalkValidation` (default `true`) | teletalk API (§6) or `StaticMethod::academicGroupSsc()` |
| `hsc_dip_board`/`hsc_dip_roll_no`/`hsc_dip_reg_no`/`hsc_dip_passing_year` | present | present | `StaticMethod::educationBoard()` |
| `hsc_dip_gpa`/`hsc_dip_group` | teletalk-only | teletalk or manual (`skipTeleTalkValidation`) | teletalk or `StaticMethod::academicGroupHsc()` |
| `diploma_trade_institute`/`diploma_trade_course`/`diploma_trade_registration_roll`/`diploma_trade_gpa` | **absent — Sailors has no diploma/trade columns at all** | present, required under both DE scenarios | `diploma_trade_course` sourced from `$courses_list` (`Eligibility`-driven `Subjects` lookup) |

### 3.3 Personal Info form

- **Controller@Method**: `actionPersonalInfo` (both), scenarios `PERSONAL_INFO`/`PERSONAL_INFO_WITH_IMAGE`, `multipart/form-data` POST (file upload).
- **`personalInformationValidate` field list** (both models apply the identical-shaped custom validator across an near-identical field set — quoted from `DeSailors.php`, the field list `model_inventory.md` reproduces verbatim):
```php
['exam_center_name','name','father_name','father_occupation','mother_name','mother_occupation',
 'current_village','current_union','current_post_office','current_thana','current_district','current_post_code',
 'father_phone','mother_phone',
 'permanent_village','permanent_union','permanent_post_office','permanent_thana','permanent_district','permanent_post_code','permanent_phone',
 'dob','age_according_to_circular','religion','marital_status','nationality',
 'is_freedom_fighter','is_child_of_naval_officer','is_anser_vdp','is_khudro_jati_gosti',
 'ssc_institute', /* DE-track adds: */ 'diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa',
 'name_bangla','father_name_bangla','mother_name_bangla']
```
Plus separate character-class validators applied identically to both models over the address/name/guardian/experience field set: `onlyInputEnglishCharacterValidation` (institute names, addresses, occupations, guardian fields, `experience_*_institute/subject/cert_name`, `naval_father_name`, `anser_vdp_rank/office_no`), `onlyNumberInputValidation` (roll/reg numbers, post codes, `experience_*_year`), `banglaInputCharacterValidation` (the three `*_name_bangla` fields), `phoneNoValidation` + `permanentPhoneUniqueCheck` (the four phone fields — `current_phone`/`permanent_phone`/`father_phone`/`mother_phone`; note `guardian_phone` is validated by `phoneNoValidation` too but is not itself `required`), `genderValidation`, `maritalStatusValidation`, `ageValidation` (batch-circular-date bound), `isNavalChildValidation` (conditionally requires `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank` when `is_child_of_naval_officer == YES`), and (Sailor only, confirmed by grep) `isAnserVdpValidation` conditionally requiring `anser_vdp_rank`/`anser_vdp_office_no` when `is_anser_vdp == YES`.

**Correction to `model_inventory.md`'s "Sailors vs. DeSailors" summary** (verified directly against `Sailors.php` source, since the summary table only describes DeSailor-only fields at a glance): the `experience_one`/`experience_two`/`experience_three`/`experience_four` (`_institute`/`_subject`/`_year`/`_cert_name`) field family, and `is_anser_vdp`/`anser_vdp_rank`/`anser_vdp_office_no`/`is_khudro_jati_gosti`, all exist as real, validated columns on **both** `Sailors` and `DeSailors` (`Sailors.php` `@property` block lines 95-123, and referenced in its own `rules()` at lines 241-346) — they are **not** DE-track-exclusive as the summary implies. The genuinely DE-exclusive fields are only the four `diploma_trade_*` columns; `Sailors` has no equivalent of those.

| Field | Required | Datasource | Dependency |
|---|---|---|---|
| `gender` | required, custom `genderValidation` | `StaticMethod::gender()` | batch eligibility-restricted |
| `marital_status` | required, custom `maritalStatusValidation` | `StaticMethod::maritalStatus()` | batch eligibility-restricted |
| `dob` | required, custom `ageValidation` | date input | bounded by batch `circular_date` |
| `current_district`/`permanent_district` | required, `integer` | `Districts::getAllActiveDistrict()` (minus the two synthetic quota-district slugs) | drives thana cascade via AJAX `upazial-by-district` |
| `current_thana`/`permanent_thana` | required, part of `personalInformationValidate` | `Upozilas::getUpazilaListCandidate($district_id)` server-render / AJAX client re-fetch | depends on paired district |
| `current_union`/`permanent_union`/`current_post_office`/`permanent_post_office` | required | `Unions::getUnionsListForCandidate($thana_id)` | depends on paired thana |
| `is_freedom_fighter` | required (part of `personalInformationValidate`); `freedom_fighter_relation` conditionally required if `YES` | `StaticMethod::yesNo()`-style | — |
| `is_child_of_naval_officer` | required; `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank` conditionally required if `YES` (`isNavalChildValidation`) | pre-filled from `CanEligibilityCheckInfo` for "posso kota" (Navy-child quota) candidates | — |
| `is_anser_vdp` | required (Sailor track: conditionally requires `anser_vdp_rank`/`anser_vdp_office_no` via `isAnserVdpValidation`) | — | — |
| `is_khudro_jati_gosti` | required, no conditional sub-fields | — | small/indigenous ethnic-group quota flag |
| `photo` | conditionally required — see §4 | file upload | dimensions locked to 300×300, ≤500KB, png/jpg |
| `diploma_trade_institute`/`_course`/`_registration_roll`/`_gpa` | **DE-track only**, part of `personalInformationValidate` | `diploma_trade_course` from `$courses_list` | — |
| `guardian_*` | not required on either model | free text | — |

### 3.4 Application Preview

No FormRequest/validation — `actionApplicationPreview` is GET-render only, `actionCompleteApplication` (its "submit" counterpart) is POST-only but validates nothing about the form body itself; it operates purely on the already-saved model, allocating roll/exam-date (§7).

---

## 4. File Upload / Cloudflare R2 Storage

Both `actionPersonalInfo` methods run an **identical two-hop upload pattern** (local temp write, then push to R2, then delete both the local temp file and the old remote object):

```php
$uniqueName = date('Ymdhms');
$sailor->photo = '/media/{sailor_candidate|de_sailor_candidate}/' . $sailor->id . '/' . $uniqueName . '.' . $fileImage->extension;
$path = $uploadBasePath . $sailor->photo;
$fileImage->saveAs($path);                                    // 1. local temp write

$cleanFileName = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileImage->baseName))) . '.' . $fileImage->extension;
$fileName = $batch_name . '/' . $sailor->id . '_' . time() . '_' . $cleanFileName;
$fileUrl = $r2Storage->uploadFile($fileName, $path);           // 2. push to R2 (bucket key includes batch name + candidate id + timestamp)
if ($fileUrl) $sailor->photo = $fileName;                      // only overwrite the stored path if the R2 upload actually succeeded

unlink($path);                                                 // 3. clean up local temp file
if (is_dir($unlink_dir)) { /* delete every remaining file in the per-candidate temp dir, then rmdir it */ }
$r2Storage->deleteFile($prevImage);                             // 4. delete the previous R2 object, if any
```

- **Directory naming diverges by track**: `/media/sailor_candidate/{id}/` for Sailor, `/media/de_sailor_candidate/{id}/` for DeSailor — both created with `mkdir(..., 0777)` if missing, before the upload branch runs.
- **Failure handling**: the whole block is wrapped in `try { ... } catch (\Exception $e) { Yii::error('Failed to upload file: ' . $e->getMessage(), __METHOD__); }` — an upload failure is logged but **does not block the save**; if `$fileUrl` comes back falsy, `$sailor->photo` simply keeps the *local* path string it was set to at the top of the block (never actually reset to `$prevImage` on failure), meaning a failed R2 upload can leave the database pointing at a path that was subsequently deleted by the `unlink()` calls immediately below — a latent broken-image-reference bug on upload failure, present identically in both controllers.
- **No file upload at all if the request has no `photo` field**: `else $sailor->photo = $prevImage;` — the existing photo is simply kept.
- **`R2Storage` component** (`common/components/R2Storage.php`, registered `Yii::$app->r2Storage`, per `service_inventory.md` §1): wraps `Aws\S3\S3Client` against a Cloudflare R2 bucket (`sailor-images`), with `verifySsl => false` and (as checked into this repo) **blank `accessKey`/`secretKey`/`endpoint`/`fileUrl`** in `common/config/main.php` — real credentials must be injected via an environment-specific override not present in this checkout. `uploadFile()`/`deleteFile()`/`fileExists()` all swallow `AwsException` and return `false` silently (the `Yii::error()` calls inside `R2Storage` itself are commented out) — so a mis-configured or down R2 bucket fails every upload/delete **silently** from the component's own perspective too, on top of the controller-level swallow described above.
- **Same component also writes the immutable per-batch candidate-log ndjson file** consumed in §7 (`upsertCandidateLog()`), and is the sole file-storage mechanism for this entire wizard — there is no local-disk-only fallback path once R2 is configured; the local temp file is always transient.

---

## 5. PII Encryption / Decryption

`common/static/DataEncryption.php` — a much smaller surface than the officer app's `AES256CTRService`, but functionally equivalent:

```php
const PREFIX = 'ENC:';
public static function getKey() { return 'KsfPndU9ciNhJG8r3QLb1q5Oocxo2I8S'; }   // hardcoded, not env-sourced

public static function dataEncrypt($data = null) {
    if (empty($data)) return $data;
    return self::PREFIX . Yii::$app->security->encryptByKey($data, self::getKey());
}
public static function dataDecrypt($data = null) {
    if (empty($data)) return $data;
    if (strpos($data, 'ENC:') === 0) return Yii::$app->security->decryptByKey(substr($data, 4), self::getKey());
    return $data;   // not prefixed → assumed already-plaintext, returned as-is
}
```

- The encryption key is a **hardcoded literal string in source**, not pulled from an environment variable or Yii params file — the same class of finding `service_inventory.md` flags for the SSLCommerz live password and the BoomCast SMS URL elsewhere in this codebase.
- `dataDecrypt()`'s `ENC:`-prefix check is what lets the codebase safely call it on fields that might be either already-encrypted or still-plaintext (e.g. legacy rows from before encryption was introduced) without double-decrypting or erroring.
- **`Sailors`/`DeSailors::$encryption_fields_for_personal_info`** (a public non-column property, identical on both models): `['father_nid', 'current_phone', 'permanent_phone', 'father_phone', 'mother_phone', 'guardian_phone']`. Both controllers iterate this list at three points in the wizard:
  1. **On GET render of `personal-info`** (both `actionPersonalInfo` methods, and also `actionApplicationPreview`/`actionDownload`/`actionVerifyCandidate`): `foreach (...) $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';` — decrypt for display.
  2. **On successful POST of `personal-info`**, immediately before `save(false)`: the identical loop but calling `dataEncrypt()` instead — re-encrypt before persisting.
  3. **Inside `actionCompleteApplication`**, when building the `$logEntry` array for the R2 audit log (§7): decrypts a *copy* (`$logEntry[$field]`, not the live model) so the ndjson log stores plaintext PII snapshots rather than the encrypted-at-rest column values — worth flagging since it means the R2 log bucket holds decrypted PII even though the database column doesn't.
- **`SailorCandidateController::actionAcademicInfo` has an extra decrypt-only pass** not present in `DeSailorController`'s equivalent: `foreach ($sailor->encryption_fields_for_personal_info as $key => $field) $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';` (`SailorCandidateController.php:231-232`, inside the `try` block right after `$sailor->load()`) — this runs *before* validation on the academic-info step, decrypting personal-info fields that this step's form doesn't even touch. `DeSailorController::actionAcademicInfo()` has no such block. The practical effect is limited (the same fields get re-encrypted again on the personal-info step regardless), but it's an asymmetry between the two controllers worth noting for anyone porting this logic.
- **`refund_phone`** (Sailor track only) is separately encrypted/decrypted outside the `encryption_fields_for_personal_info` loop — `actionPayment()` decrypts it once for display (`SailorCandidateController.php:181`) and `actionRefundPhone()` encrypts it on save (§1.8) — it is not part of the shared PII list because it isn't a `personal_info`-scenario field.

---

## 6. External SSC/HSC Result API (Teletalk)

`common\static\StaticMethod::educationBoardResult(string $examType, string $board, $roll_no, $year, $reg_no = NULL)` (`common/static/StaticMethod.php:531-554`):

```php
curl_setopt($ch, CURLOPT_URL, "http://ebapi.teletalk.com.bd//v1.0/ebapi.php");   // plain HTTP, doubled slash, literal in source
$post = json_encode([
    'commandID' => 'getDetailsResult', 'exam' => $examType, 'board' => $board,
    'rollNo' => $roll_no, 'year' => $year, 'regNo' => $reg_no,
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$headers = ['Content-Type: application/json', 'APIKEY: 46f4dd2a52453d4ef3fc137b65ee10040e73638e', 'Content-Length: ' . strlen($post)];
$server_output = curl_exec($ch);
return json_decode($server_output);
```

- **Plain `http://`, not `https://`**, and a **hardcoded API key literal in source** — the same class of hardening finding as the SSLCommerz live credentials and the `DataEncryption` key. `CURLOPT_SSL_VERIFYPEER => false` is set anyway even though the URL isn't TLS at all, suggesting this was copy-pasted from the same curl-boilerplate pattern used elsewhere in the codebase (SSLCommerz, BoomCast SMS) rather than deliberately tuned per-endpoint.
- **Called synchronously inside the academic-info POST handler**, both tracks, for both SSC and HSC independently, whenever the corresponding board/roll/reg/year fields are all non-empty: `StaticMethod::educationBoardResult(examType: 'ssc'|'hsc', board:..., roll_no:..., year:..., reg_no:...)`.
- **Response shape** (cast to array as `$resultArr`): `responseDesc` (`'Success'` on a hit), `result` (pass-code, checked against `'P'|'PA'|'PB'|'PC'` for SSC, `'P'|'PA'|'PB'` for HSC — the accepted code set differs slightly by exam level), `iName` (institute name), `studGroup`, `regNo`, `passYear`, `gpa`, `rollNo`, `name`, `father`, `mother`, `gender` (`'MALE'`/`'0'`/`'1'` all map to `GENDER_MALE`, anything else to `GENDER_FEMALE` — a slightly odd three-way equivalence class rather than a clean boolean), `dob` (`DDMMYYYY` packed string, re-split into `Y-m-d` in the controller).
- **Tamper-detection on SSC only, Sailor track only**: after a successful SSC lookup, `SailorCandidateController` cross-checks the API's `rollNo`/`passYear`/`regNo`/`board` against what the candidate submitted and rejects with a flash error if any mismatch (`SailorCandidateController.php:260-263`) — **`DeSailorController`'s SSC branch has this exact check commented out** (`DeSailorController.php:328-332`, the `if(...)/{...}` block is entirely `//`-prefixed) — so a DE-track candidate's submitted board/roll/reg/year values are not cross-verified against the teletalk response at all, only used to *fetch* it; whatever the API returns is trusted and written back regardless of what was typed. **Neither track cross-checks HSC results** the same way — the tamper-check exists only for SSC, and only (functionally) on the Sailor track.
- On a lookup failure (`responseDesc != 'Success'` or an unrecognized result code) for SSC, both controllers flash an error and **redirect back to the same academic-info step** (full page reload, form data lost) rather than re-rendering the form with an inline error — a rougher UX than the officer app's teletalk-lookup AJAX buttons, which fail inline without losing the rest of the form.
- **DE-track opt-out**: `DeSailors::$skipTeleTalkValidation` defaults `true`; when set, the entire teletalk call is bypassed and the candidate self-reports GPA/group (§3.2), with the eligibility GPA/group cross-check still applied against the self-reported values (`DeSailorController.php:310-320`) — so the eligibility floor is still enforced even without the external API, just against unverified candidate input instead of a verified result.

---

## 7. Roll-Number / Exam-Date Allocation Logic — `actionCompleteApplication`

The single most complex block of logic in either controller (~240 lines each), run **synchronously inline on the final POST** — there is no queue/job layer in this app at all (`service_inventory.md` §5 confirms zero `yii\queue` usage anywhere), unlike the officer app's `ProcessRollGeneration::dispatch()`. The two controllers' implementations are near-identical; differences are called out inline.

**Guard clauses** (both): re-loads `$batch_setting_info`, re-checks `SailorBatchs::isCandidateContinueApplication()`, requires POST (`BadRequestHttpException` otherwise), and short-circuits to `/my-application` if `$sailor->exam_date || $sailor->serial_no` already set (idempotency guard — re-POSTing after a roll was already assigned just advances `phase` to `SAILOR_PHASE_FIVE` again and redirects, doesn't re-allocate).

**Allocation only runs if `$batch_setting_info['roll_from'] == Constants::ROLL_FROM_BATCH`** — the `else` branch is a bare comment `/// no need now` with no code, meaning any batch configured with a different `roll_from` value silently does nothing on submit (no error, no redirect, no roll assigned — the request just falls through to the end of the method with no response written, which in Yii2 renders an empty body). This is true of both controllers identically.

1. **Resolve the candidate's matching batch configuration(s)**: `SailorBatchConfiguration::configurationByBatchCenterGenderCanDesigDistrictSlugAll(batch_id, center_id, gender, candidate_designation, eligible_district)`. Empty result → flash "Configuration missing please contact with support**.1**" (Sailor — note the stray `.1` typo) / "...support" (DeSailor, no typo) and redirect to `/my-application`.
2. **Get the base roll number**:
   - Sailor: `Sailors::nextRollByBatchId(batchId, batch_setting_info_roll_no: $batch_setting_info['start_roll'])` returns an **array** with a `['roll_no']` key. Sailor-only extra step: if the batch defines `next_start_roll` **and** `next_start_roll_after`, and the computed roll number exactly equals `next_start_roll_after + 1`, the roll number is **overridden** to jump to `next_start_roll` instead — a manual "skip a numeric gap" mechanism (e.g. reserving a block of numbers, or resuming numbering after a manually-assigned VIP/reference block) that has **no equivalent in `DeSailorController`**.
   - DeSailor: `DeSailors::nextRollByBatchId(batchId, batch_setting_info_roll_no: $batch_setting_info['start_roll'])` returns a **plain scalar**, not an array — confirmed by usage (`$roll_no = DeSailors::nextRollByBatchId(...)` with no `['roll_no']` subscript) — so despite sharing a method name and near-identical purpose, the two models' `nextRollByBatchId()` helpers have **different return shapes**, and no `next_start_roll` override logic exists on the DE track at all.
3. **Resolve which configuration group to use, when more than one matches**:
   - If `count($configuration_model) > 1`: takes the first (ascending exam-group order) config; if that config has `roll_swap_in_group == YES`, collects *every* config row in the result set that also has `roll_swap_in_group == YES` into a candidate pool (`$configuration_ids`), otherwise the pool is just that one config. Counts current candidates per config (`sailorBatchAndGroupWiseCount()` / `DeSailors::sailorBatchAndGroupWiseCount()`), deactivates (`status = NO`) any config whose `group_no_of_candidate` cap is already met, and picks the **least-filled remaining config** (`min()` over the remaining counts) — a simple load-balancing distribution across parallel exam groups/branches sharing the same roll-swap pool.
   - If only one config matches: still checks its `group_no_of_candidate` cap; if already full, deactivates it and bails out with "Configuration missing please contact with support" / "Internal Error, Please Try Again Later." (the two error strings are used in slightly different branches of the same logic, again identical text between the two controllers except for the Sailor-only `.1` typo noted above).
4. **Resolve exam date**: builds `$canInfo = ['center_id', 'gender', 'candidate_designation' => $configuration_model['candidate_designation']]`, calls `{Sailors|DeSailors}::getLastRollExamDateByDesignationCenter(batchId, canInfo)` to find the most recently used exam date for this designation/center combination (round-robin continuation point), then `SailorBatchConfigurationExamDate::getListByConfigurationId()` for the active date list on this config. If a "last used" date exists, `getNextKeyValue()` advances to the *next* date in the list (wrapping); otherwise starts from the first date in the list.
5. **Max-candidates-per-exam-day enforcement**: only if `configuration_model['check_max_candidate'] == YES`. If `max_candidate_this_date > 0`, counts already-assigned candidates for that exact date (`getTotalCandidateByExamDate()`) and, once at/over the cap, calls `SailorBatchConfigurationExamDate::getNextAvailableExamDate()` to roll forward to the next date under capacity (circularly), bailing to "Configuration missing please contact with support" if none is available. If `max_candidate_this_date` is `0` (not configured), it bails immediately with the same message rather than treating "0" as "uncapped" — an intentional fail-closed default, identical in both controllers.
6. **Commit**: sets `exam_date`, `exam_date_id`, `exam_group`, `batch_config_id`, `serial_no = strval($roll_no)`, `serial_generate_date = date('Y-m-d')`, `phase = SAILOR_PHASE_FIVE`. **Sailor-only**: also sets `$sailor->team = $configuration_model['team'];` — DeSailor's model/table has no equivalent commit of a `team` value from the configuration (whether or not `DeSailors` even has a `team` column was not separately verified here, but the controller code simply never assigns one).
7. **On successful `save(false)`** (both, identical shape): builds `$logEntry = $sailor->toArray()`, decrypts the PII fields in that copy (§5, point 3), resolves a filesystem-safe `$batch_name` from `batch_setting_info['name_en']`, and calls `Yii::$app->r2Storage->upsertCandidateLog(model: $logEntry, logFile: "{batch_name}.ndjson")` — an append-or-merge audit trail keyed by candidate id, one ndjson file per batch, in the R2 bucket (§4). Then calls `{Sailors|DeSailors}::generateLog($sailor->id)` — a **second**, separate logging call: per `model_inventory.md`, `Sailors::generateLog()` "dumps a fixed field list to a per-batch JSON debug log under `@rootDirFilUpload/dummy/{batch_id}.txt`" — i.e. a **local-disk** debug dump, distinct from and in addition to the R2 ndjson audit trail, on every successful roll assignment.
8. **Sailor-only dead SMS call**: immediately after `generateLog()`, `SailorCandidateController.php:900-902` has a fully commented-out `SendSms::sendSms(mobile: $sailor->permanent_phone, smsBody: $smsBody, serial_no: $sailor->serial_no, application_type: 'sailor')` — confirms `service_inventory.md`'s finding that SMS notification on roll assignment is disabled in this codebase (the officer app's equivalent job actively sends a congratulations SMS; here the call site exists but is dead). **`DeSailorController` doesn't even import `SendSms`** — the DE track never had this call in the first place, not merely a commented-out one.
9. Redirects to `{sailor-candidate|de-sailor}/download`.

---

## 8. Sailor vs. DE-Sailor — full structural diff

| Aspect | `SailorCandidateController` / `Sailors` | `DeSailorController` / `DeSailors` |
|---|---|---|
| Phases | 5 (`SAILOR_PHASE_ONE`..`FIVE`) | same 5, same constants (shared `Constants` class) |
| `AccessControl` | partial — `payment`/`academic-info` only | **none at all** |
| Payment `tran_id` format | `date('Ymdhis') . rand(111,999) . id` | `date('ymdhis') . rand(111111,999999)` (lowercase `y`, no candidate id suffix) |
| Payment retry `opt_a` | `'sailor'` | `'de_sailor'` |
| Refund-phone modal on payment page | present (conditional on `allow_refund`) | **absent** |
| `actionRefundPhone` | exists | **does not exist** |
| `actionCancelApplication` | exists | **does not exist** |
| Teletalk skip option | not available — teletalk is always attempted when fields are present | `$skipTeleTalkValidation` (default `true`) lets the candidate self-report GPA/group |
| SSC tamper cross-check (teletalk result vs submitted values) | enforced | present in source but **commented out** — not enforced |
| TEC-board + Medical-designation GPA carve-out | present | absent |
| Diploma/Trade fields (`diploma_trade_*`) | **absent — no such columns** | present, required under both DE academic scenarios |
| `experience_one..four_*`, `is_anser_vdp`, `is_khudro_jati_gosti` | **present on both** (correction to a same-titled inventory summary — see §3.3) | present |
| `actionAcademicInfo` decrypt-before-validate pass | present (redundant, decrypts fields this step doesn't touch) | absent |
| `actionPersonalInfo` duplicate `saveAs()` call after redirect-path save | absent | present (likely leftover/dead, §1.3) |
| `nextRollByBatchId()` return shape | array, keyed `['roll_no']` | scalar |
| `next_start_roll`/`next_start_roll_after` roll-jump override | present | absent |
| `team` assignment in `actionCompleteApplication` | present | absent |
| "Configuration missing" flash text | has a stray `.1` suffix in one branch | clean text, no typo |
| Commented-out `SendSms::sendSms()` call | present (dead) | `SendSms` not even imported |
| `actionDownloadForm` phone `+88` prefixing | present | absent |
| `actionVerifyCandidate` dead PDF block | 17 lines after `return` (`:1051-1067`) | 18 lines after `return` (`:1075-1092`) — same shape, one line longer |
| Upload directory | `/media/sailor_candidate/{id}/` | `/media/de_sailor_candidate/{id}/` |
| View sizes (`view_inventory.md`) | `personal_info.php` 1025 lines, `academic_info.php` 201 lines | `personal_info.php` 1074 lines, `academic_info.php` 275 lines (+diploma/trade block + teletalk-skip manual-entry columns) |

---

## 9. Cross-cutting notes

- **No `FormRequest`/step-specific validator classes exist in this app** — every validation rule for every wizard step lives in one place per track (`Sailors::rules()` / `DeSailors::rules()`), scenario-gated via Yii2's built-in `on => self::SCENARIO` mechanism. This is a real architectural difference from the officer app's per-step `AcademicInfoRequest`/`PersonalInfoRequest`/`IssbRequest`/`PaymentRequest` classes, not a gap — confirmed by `controller_inventory.md`'s framing note at the top of this repo's inventory set.
- **Debug scaffolding (`echo`/`print_r`/`die()`) is live in every validation-failure branch** of both controllers' four main wizard-step POST handlers (`payment`, `academic-info`, `personal-info`, `complete-application`) — not flagged as dead code (it *is* reachable, on any validation failure) but is production-inappropriate raw error output that would leak internal model state/stack context to any candidate who submits invalid data, and hard-terminates the request (`die()`) rather than returning a normal Yii2 error response.
- **`actionCompleteApplication`'s roll-allocation logic has no explicit transaction/locking around the "count candidates in this config → compare to cap → deactivate if full" read-then-write sequence** in either controller — under concurrent submissions near a cap boundary, this is susceptible to the classic check-then-act race (two candidates could both read "count = cap-1" and both be assigned before either write lands), though verifying that empirically is out of scope for a static read.
- **The R2 audit-log write (`upsertCandidateLog`) stores decrypted PII** in the ndjson log file even though the source-of-truth database column is encrypted at rest (§5) — anyone with read access to the R2 bucket's log path sees plaintext phone numbers/NID, independent of the encryption applied to the `sailors`/`de_sailors` tables themselves.
- **Two independent logging mechanisms fire on every successful roll assignment**: the R2 `upsertCandidateLog()` ndjson (cloud) and `{Sailors|DeSailors}::generateLog()` (local-disk debug dump under `@rootDirFilUpload/dummy/`) — the latter is described in `model_inventory.md` as a "debug log," suggesting it may be leftover development instrumentation never removed rather than an intentional second audit trail.
- **Neither controller's `actionVerifyCandidate()` nor `actionDownloadForm()` performs an ownership check** (`findModel()` is bypassed in favor of a direct unscoped `::find()->where(['id'=>...])->one()`) — by design, since both are meant to be reachable by anyone holding the encrypted slug (e.g. a printed form's QR/verification link), but worth noting alongside the encrypted-slug-as-capability-token pattern this implies: possession of the slug is the entire access control for these two endpoints.
