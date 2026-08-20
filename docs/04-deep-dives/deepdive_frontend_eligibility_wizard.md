# Deep Dive: Frontend — Pre-Application Eligibility Checker Wizard

Scope: `frontend/views/check-eligibility/*.php`, `frontend/controllers/CheckEligibilityController.php`, `common/models/CanEligibilityCheckInfo.php`, `common/models/Eligibility.php`, plus the hand-off point into `frontend/controllers/CandidateController.php::haveCeci()`.

All evidence below is cited with exact file path and line numbers / quoted code, verified by reading the actual files on disk (no assumptions). This is a Yii2 application, not Laravel — there are no separate FormRequest classes; every validation rule lives in `CanEligibilityCheckInfo::rules()`, scenario-scoped, and forms are plain `ActiveForm` (server-rendered, non-AJAX submit) rather than a JSON-driven SPA-style wizard.

---

## 0. Routes in scope

Source: `frontend/config/main.php:20` (`'defaultRoute' => 'check-eligibility/index'`) and `frontend/config/main.php:59-66` (`urlManager` — `enablePrettyUrl: true`, `showScriptName: false`, **`'rules' => []`**, i.e. no custom route rules; every eligibility URL is Yii's default `controller-id/action-id` pretty-URL mapping). No auth/access-control `behaviors()` are defined on `CheckEligibilityController` at all — the entire wizard is public/unauthenticated by construction, and `beforeAction()` only turns CSRF off for one action:

```php
// frontend/controllers/CheckEligibilityController.php:23-29
public function beforeAction($action)
{
    if ($action->id == 'get-description') {
        $this->enableCsrfValidation = false;
    }
    return parent::beforeAction($action);
}
```

| Method | URL | Action |
|---|---|---|
| GET / POST | `/` (site root — `defaultRoute`) and `/check-eligibility/index` | `actionIndex()` |
| GET / POST | `/check-eligibility/academic-info?slug=...` | `actionAcademicInfo($slug)` |
| GET | `/check-eligibility/eligible-department?slug=...` | `actionEligibleDepartment($slug)` |
| GET | `/check-eligibility/apply-department?slug=...&adpt=...` | `actionApplyDepartment($slug, $adpt)` |
| POST (AJAX, CSRF-exempt) | `/check-eligibility/get-description` | `actionGetDescription()` |

The wizard is a 3-step public flow — **Step 1 Personal Details → Step 2 Academic Details → Step 3 Available Position (Eligible Department)** — followed by an "Apply" action per eligible department that hands off to candidate signup (`candidate/sign-up?ceci=...`) for guests, or directly creates a `Sailors`/`DeSailors` application row for an already-authenticated candidate.

The identity of the `CanEligibilityCheckInfo` record created in Step 1 is passed between steps as an encrypted `slug` query parameter, generated/decrypted by `common\static\StaticMethod::encryptPk()` / `decryptPk()` (`common/static/StaticMethod.php:486`, `508`).

---

## 1. Page Inventory

### Page 1 — Check Eligibility: Personal Details (Step 1)

| | |
|---|---|
| Page Name | Check Eligibility — `<h1>` text "CHECK ELIGIBILITY" (uppercased via CSS) |
| URL | `GET/POST /` (site default route) or `/check-eligibility/index` |
| Portal | Frontend (public, unauthenticated) |
| Access control | None — no `behaviors()` on the controller |
| View File | `frontend/views/check-eligibility/personal_info.php` |
| Controller | `frontend\controllers\CheckEligibilityController` |
| Controller Method | `actionIndex()` (`CheckEligibilityController.php:36-84`) |
| Purpose | First step of the wizard: collect DOB, gender, marital status, height (ft/in), chest measurements, eye status, and district. Creates the working `CanEligibilityCheckInfo` row. |
| Detailed Description | Guards against a stale session first: if the visitor is logged in but their `Session` row for the current PHP session id has `expire != 2`, they are force-logged-out (`actionIndex()`, lines 39-45). A new `CanEligibilityCheckInfo` is instantiated with `apply_department_type` hard-set to `Constants::CANDIDATE_SAILOR` (1) and scenario `SCENARIO_PERSONAL_INFO`. On POST + validation pass: `height` is recomputed server-side from `height_feet`/`height_inch` via `StaticMethod::heightChangeFeetInchToCM()`, `dob` is reformatted from the picker's `dd-MM-yyyy` to `Y-m-d`, the model is saved, and the browser is redirected (a normal HTTP redirect, not JSON/AJAX) to Step 2 with the encrypted new row id as `slug`. If the visitor is already logged in with a stored DOB, that DOB pre-fills the field (lines 74-76: `if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->dob)) $model->dob = Yii::$app->user->identity->dob;`) — this runs *after* the POST block, so it only affects the initial GET render, never a validation-failure redisplay. |
| User Actions Available | Date of Birth (jQuery UI `DatePicker`, readonly text input); Gender dropdown; Nationality dropdown (single fixed option); Marital Status dropdown; Height Feet / Height Inch text inputs with live JS-calculated CM display; Chest Normal / Chest Expanded text inputs; Eye Power dropdown (single fixed option); District select2 searchable dropdown (populated by an AJAX handler keyed off `candidate_type`, which is itself a **hidden, fixed-value field** — see §3 below); submit button "Continue". A second, unrelated "Check Eligibility" CTA opens a promotional Bootstrap modal (`#staticBackdrop-eligibility`) further down the page. |
| Partials/Widgets | `frontend\components\SupportNo::widget(['steps'=>[1], 'slug'=>..., 'show_form_text'=>false])` (line 104) — renders a support-hotline banner keyed to the current step; the 3-step wizard progress indicator itself is **inlined directly in the view** (lines 113-148), not a shared partial. |
| JS on this page | Two inline `<script>` blocks (lines 20-101): a `#caneligibilitycheckinfo-candidate_type` change handler that AJAX-fetches districts by candidate type from `/ajax/district-by-candidate-type` and shows/hides a "PO Number/Rank" block (dead code path — see §3), and `heihtCalculation()` for the live Height-in-CM display. No external page-specific JS file is loaded. |
| Submit mechanism | **Plain server-rendered POST**, not AJAX — the form has no `enableAjaxValidation` at the form level; on validation failure Yii re-renders `personal_info` with inline field errors, on success it issues a real HTTP redirect to Step 2. (`ActiveForm::begin()` at line 151 sets `'enableClientValidation' => false, 'validateOnBlur' => false`.) |
| Modal on this page | **"Check Eligibility" promo modal**, `id="staticBackdrop-eligibility"` (`personal_info.php:444-460`). Trigger: `<button data-bs-toggle="modal" data-bs-target="#staticBackdrop-eligibility">Check Eligibility</button>` inside the "Confused about Your Eligibility?" band. Body: a single static `<img src=".../navy/images/eligibility.png">`, no fields, no validation. Buttons: only a header close (`×`, `data-bs-dismiss="modal"`). No backend call, no navigation — `data-bs-backdrop="static" data-bs-keyboard="false"` forces the close button. Purely decorative. |

---

### Page 2 — Check Eligibility: Academic Details (Step 2)

| | |
|---|---|
| Page Name | Check Eligibility — same `<h1>` "CHECK ELIGIBILITY" text; browser `<title>` is "Check Eligibility" (`academic_info.php:13`, the only page of the three that sets a distinct title) |
| URL | `GET/POST /check-eligibility/academic-info?slug=...` |
| Portal | Frontend (public) |
| Access control | None |
| View File | `frontend/views/check-eligibility/academic_info.php` |
| Controller Method | `actionAcademicInfo($slug)` (`CheckEligibilityController.php:91-124`) |
| Purpose | Step 2 of the wizard: collect JSC pass/fail, SSC/equivalent group+GPA, trade-course completion (+ subject + prior experience), biology inclusion, and HSC-or-Diploma group+result — used later to score eligibility. **Unlike the sister officer-recruitment wizard, there is no Honours/Masters/PHD section anywhere in the sailor wizard** — the model has `honours_*`/`masters_*` columns (see `CanEligibilityCheckInfo` docblock) but this view never renders them, so they are always null for every eligibility-check row created here. |
| Detailed Description | `findModel($slug)` decrypts the slug and loads the Step-1 row (404 via `NotFoundHttpException` if missing — `CheckEligibilityController.php:480-486`), sets scenario `SCENARIO_ACADEMIC_INFO`. On POST + validation pass, the model is saved and the browser is redirected to Step 3 with the same encrypted id. **On validation failure the controller does not re-render — it dumps and halts** (see §5 Debug Leftovers). |
| User Actions Available | JSC Result radio (Pass/Fail); SSC Academic Group dropdown + SSC GPA text input; "Trade Course Completed (Minimum 6 Months)?" Yes/No radio, which conditionally reveals a Trade-Course-Subject dropdown and (nested inside that) a "Do you have trade course work experience?" Yes/No radio; a "Biology Included" Yes/No radio block shown only for Science/Vocational SSC groups; HSC/Diploma radio (toggles the label and, via AJAX, the options of the group dropdown between HSC academic groups and Diploma courses); HSC/Diploma Group dropdown; HSC/Diploma Result text input; submit "Continue". |
| Partials/Widgets | Same `SupportNo::widget(...)` banner (line 120); wizard progress indicator again inlined in-page (lines 133-168, step 2 marked active alongside step 1). |
| JS on this page | Inline `<script>` (lines 16-115): `#caneligibilitycheckinfo-ssc_equv_group` change handler that shows/hides the trade-course and biology-inclusion blocks and auto-checks/unchecks the trade-course radio depending on Science vs Vocational vs other; `.is_trade_course_complete_common_class` click handler toggling the trade-subject dropdown and experience radio; `.hsc_eqv_ac_type_common_class` click handler that AJAX-fetches the HSC-or-Diploma group option list from `/ajax/hsc-diploma-ac-group` and relabels the field/placeholder ("HSC Result" vs "Diploma Result"). No external page-specific JS file. |
| Submit mechanism | Plain server-rendered POST (same pattern as Step 1; no `enableAjaxValidation` set at the form level, though several individual fields — `ssc_equv_group`, `ssc_equv_result`, `ssc_equv_is_trade_course_complete`, `trade_course_subject`, `have_trade_course_experience`, `ssc_equv_is_biology_include`, `hsc_equv_result` — are marked `['enableAjaxValidation' => true]` for live blur-validation of just those fields). |
| Modal on this page | The identical "Check Eligibility" promo modal (`academic_info.php:389-406`) is duplicated verbatim in this view too, same id `staticBackdrop-eligibility`, same behavior as Page 1's. |

---

### Page 3 — Eligible Department (Step 3, results)

| | |
|---|---|
| Page Name | Eligible Department |
| URL | `GET /check-eligibility/eligible-department?slug=...` |
| Portal | Frontend (public) |
| Access control | None |
| View File | `frontend/views/check-eligibility/eligible_department.php` |
| Controller Method | `actionEligibleDepartment($slug)` (`CheckEligibilityController.php:130-367`) |
| Purpose | Runs the eligibility-scoring engine against the candidate's Step 1 + Step 2 answers, `eligibility` config rows, active `SailorBatchs`, and `SailorBatchConfiguration` district/gender rules, then lists the department(s) the candidate qualifies for with per-row "Apply" links. |
| Detailed Description | Display-only results page — no form. Re-fetches only a fixed column subset of the model directly by decrypted id (**not** via `findModel()`, and with no scenario set — see quoted select at lines 136-159). Shows the candidate's DOB and, for each active batch, a computed age string ("X years Y months Z days"), then either a results table or a "not eligible" message. See §4 for the full scoring algorithm. |
| User Actions Available | Per eligible-department row: an "Apply" link (`common-btn bg-yellow`) to `check-eligibility/apply-department?slug=...&adpt=<encrypted designation id>`; a "Details" text link per row (when a description exists) that opens a Bootstrap modal via AJAX. No filters/tabs. |
| Partials/Widgets | Same `SupportNo::widget(...)` banner (line 14); wizard progress indicator inlined again, all 3 steps marked active (lines 31-66). |
| JS on this page | Inline `<script>` (lines 165-188): `.show_modal` click handler that AJAX-POSTs to `check-eligibility/get-description` with the designation id and injects the returned `name_en`/`description` into a shared `#exampleModal` Bootstrap modal. |
| AJAX endpoints it calls | `POST /check-eligibility/get-description` (CSRF-exempt, `actionGetDescription()`) for the department-description modal only. The "Apply" action itself is a normal `<a href>` GET navigation, not AJAX. |
| Modal on this page | `#exampleModal` (lines 191-202) — generic Bootstrap modal shell populated by JS from the AJAX response; body starts empty, title starts "Modal title" and is overwritten client-side. No form, no validation, no backend write — purely a read-only description viewer. |

---

### Non-page action — Apply Department (hand-off)

| | |
|---|---|
| URL | `GET /check-eligibility/apply-department?slug=...&adpt=...` |
| Controller Method | `actionApplyDepartment($slug = null, $adpt = null)` (`CheckEligibilityController.php:372-457`) |
| Purpose | Validates the clicked department against the session-stored eligible-department list from Step 3, resolves batch/center configuration, persists the choice on the `CanEligibilityCheckInfo` row, then either redirects a guest into candidate sign-up (carrying `ceci`) or, for an already-authenticated user, creates the `Sailors`/`DeSailors` application row directly and redirects to payment. See §4.5 for the full flow and §5 for its debug leftover. |

### Non-page action — Get Description (AJAX)

| | |
|---|---|
| URL | `POST /check-eligibility/get-description` (CSRF-exempt) |
| Controller Method | `actionGetDescription()` (`CheckEligibilityController.php:463-471`) |
| Purpose | Returns a `CanDesignation`'s `name_en`/`description` as raw JSON for the Step-3 "Details" modal. Has an unreachable `exit();` after `return` — see §5. |

---

## 2. Frontend File Mapping

| Concern | File(s) |
|---|---|
| Step 1 view | `frontend/views/check-eligibility/personal_info.php` |
| Step 2 view | `frontend/views/check-eligibility/academic_info.php` |
| Step 3 view (results) | `frontend/views/check-eligibility/eligible_department.php` |
| Controller | `frontend/controllers/CheckEligibilityController.php` |
| Working record model | `common/models/CanEligibilityCheckInfo.php` |
| Eligibility config model (scoring rows, admin-managed) | `common/models/Eligibility.php` |
| Active batch / age-window model | `common/models/SailorBatchs.php` + `common/models/scopeQuery/SailorBatchs.php` (query scopes) |
| District/gender/batch config model | `common/models/SailorBatchConfiguration.php` |
| Sign-up hand-off | `frontend/controllers/CandidateController.php` (`actionSignUp()`, `haveCeci()`) |
| Applied-to models | `common/models/Sailors.php`, `common/models/DeSailors.php` |
| Helper/constants classes | `common\static\StaticMethod` (dropdown option lists, `encryptPk`/`decryptPk`, height conversion, age-diff calculation), `common\static\Constants` (all magic-number enums referenced below) |

---

## 3. Form Documentation

### Form 1 — Personal Details

- **Page:** `personal_info.php`
- **Action URL:** same URL, plain POST (self-submit — no separate `-store` route)
- **Controller@Method:** `CheckEligibilityController@actionIndex`
- **Validation:** `CanEligibilityCheckInfo::rules()`, scenario `SCENARIO_PERSONAL_INFO`

Exact scenario-relevant rules (`common/models/CanEligibilityCheckInfo.php:75-105`):
```php
[['candidate_type', 'dob', 'gender', 'district', 'height_feet', 'height_inch', 'marital_status'], 'required', 'on' => self::SCENARIO_PERSONAL_INFO],
[['p_o_no', 'rank'], 'validatePoNoRank', 'skipOnError' => false, 'skipOnEmpty' => false],
[['p_o_no', 'rank'], 'validateEnglishInput', 'skipOnError' => false, 'skipOnEmpty' => false],
['height_feet', 'integer', 'min' => 5, 'max' => 7],
['height_inch', 'integer', 'min' => 0, 'max' => 11],
[['chest_normal', 'chest_expanded'], 'integer', 'min' => 25, 'max' => 38],
```
Custom validator, `validatePoNoRank()` (lines 108-114):
```php
public function validatePoNoRank($attribute, $params, $validator)
{
    if (empty($this->$attribute) && in_array($this->candidate_type, [Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA, Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL])) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
```

#### Fields

| Field | Input Type | Required? | Default | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|---|
| `candidate_type` | **hidden input, fixed value `1`** (`personal_info.php:187`: `$form->field($model, 'candidate_type')->hiddenInput(['value' => 1])->label(false)`) | Required (always satisfied — always `1`) | `1` (General) | `required` | n/a — the real `<select>` markup for candidate type (`StaticMethod::candidateTypeForEligibilityCheck()`) is commented out (lines 170-185) | **Every row created by this wizard is `candidate_type = ELIGIBILITY_CANDIDATE_TYPE_GENERAL (1)`.** The "Posso Kota"/"Departmental" candidate types (2, 3) and their `p_o_no`/`rank` requirement in `validatePoNoRank()` are unreachable from this UI — the block that would show/hide a PO-Number/Rank input pair is likewise commented out (`personal_info.php:189-204`), even though its JS toggle handler is still live and wired to a `#caneligibilitycheckinfo-candidate_type` element that no longer exists on the page (dead JS, see conditional behavior #1 below). |
| `dob` | `<input readonly>` + jQuery UI `DatePicker`, format `dd-MM-yyyy` | Required | Auth user's stored DOB if logged in (assigned *after* the POST-handling block, so only applies on fresh GET, not on redisplay-after-error) | `required` (date parsed via `date()`/`strtotime()` server-side, not format-validated beyond that) | n/a — `yearRange` client option restricts the picker to `now-35y : now-15y` | Height/DOB independent; server reformats `dd-MM-yyyy` → `Y-m-d` before save (`actionIndex()` line 59). |
| `gender` | `<select>` | Required | none | `required` (no `in:` restriction in model rules; `integer` type-check applies via the big shared integer rule at line 97) | `StaticMethod::gender()` — static Male(1)/Female(2) | Drives Step-3 `height_male`/`height_female` and `gender` REGEXP matching in the scoring engine. |
| `nationality` | `<select>` | **Not validated** — no rule for `nationality` in `rules()` at all | `bangladeshi` (only option) | none | static single `<option value="bangladeshi">Bangladeshi</option>` (`personal_info.php:241`) | Decorative/locked field, same pattern as the officer wizard. |
| `marital_status` | `<select>` | Required | none | `required` (`integer` type-check via shared rule) | `StaticMethod::maritalStatus()` — static Married(1)/Unmarried(2) | Feeds Step-3 `marital_status` REGEXP match. |
| `height_feet` | `<input type="text">` | Required | none | `required` + `integer, min:5, max:7` | n/a | Combined with `height_inch` server-side into `height` (cm) via `StaticMethod::heightChangeFeetInchToCM()`; also drives the live JS "X feet Y inch or Z CM" display (`heihtCalculation()`, lines 87-100). |
| `height_inch` | `<input type="text">` | Required | none | `required` + `integer, min:0, max:11` | n/a | Same live-calc dependency. |
| `chest_normal` | `<input type="text">` | **Not required** (present in view, but the model rule is only `integer, min:25, max:38` — no `required` for this scenario) | none | `integer, min:25, max:38` (only enforced if a value is supplied) | n/a | None — collected but never referenced anywhere in the Step-3 scoring engine (`Eligibility` has `chest_normal_male`/`chest_extended_male`/etc. columns, but `actionEligibleDepartment()` never selects or compares chest values). |
| `chest_expanded` | `<input type="text">` | Same as `chest_normal` — not required | none | `integer, min:25, max:38` | n/a | Same — unused downstream in this wizard's scoring. |
| `eye_status` | `<select>` | **Not required** (no rule beyond the shared `integer` type-check) | none | none beyond `integer` | `StaticMethod::candidateEyeStatus()` — **only one live option, `6/6`**; the `6/12` option is commented out (`StaticMethod.php:370-377`) | Collected but, like chest, never read by the Step-3 scoring query — `Eligibility` has no eye-related column at all. |
| `district` | `<select class="select2">` | Required | none | `required` — **no `exists` DB-integrity rule** (unlike the sister officer wizard's `Rule::in`/`exists` checks) | `District::getAllActiveDistrict()` → `Districts::find()->where('status'=>1)->orderBy('name_en ASC')`, keyed `slug => name_en` (`common/models/Districts.php:118-125`) | Feeds the Step-3 `SailorBatchConfiguration` district-slug REGEXP match. Static Bangla warning text below the field: "আবেদনের সময় স্থায়ী জেলা পরিবর্তন করা যাবে না।" (permanent district cannot be changed at application time). |

#### Conditional behavior (evidence-quoted)

1. **Dead JS: candidate-type change handler targets a field that no longer renders.** `personal_info.php:25-64`:
```js
$('#caneligibilitycheckinfo-candidate_type').change(function() {
    let candidate_type = $(this).val(); // Candidate type
    let posso_kotha_dept_candidate = [2, 3] // Posso kotha and departmental candidate type
    if (posso_kotha_dept_candidate.includes(Number(candidate_type))) {
        $("#posso_kota_dept_can_block").show()
    } else {
        $('#caneligibilitycheckinfo-p_o_no').val('')
        $('#caneligibilitycheckinfo-rank').val('')
        $("#posso_kota_dept_can_block").hide();
    }
    $.ajax({
        url: '<?php echo Yii::$app->request->baseUrl . '/ajax/district-by-candidate-type' ?>',
        ...
    });
})
```
Since `candidate_type` is now rendered as a plain `hiddenInput` (no `change` event ever fires from user interaction) and the `#posso_kota_dept_can_block`/`p_o_no`/`rank` markup is commented out of the page, this entire handler — including the AJAX district-reload call — is unreachable dead code left over from when candidate-type selection was live.
2. **Live height CM calculation** (display-only) — `personal_info.php:66-100`, `heihtCalculation()`. Server independently recomputes cm on submit via `StaticMethod::heightChangeFeetInchToCM()` (`CheckEligibilityController.php:58`).

---

### Form 2 — Academic Details

- **Page:** `academic_info.php`
- **Action URL:** same URL, plain POST (self-submit)
- **Controller@Method:** `CheckEligibilityController@actionAcademicInfo`
- **Validation:** `CanEligibilityCheckInfo::rules()`, scenario `SCENARIO_ACADEMIC_INFO`

Exact scenario-relevant + always-on rules (`CanEligibilityCheckInfo.php:75-105`):
```php
[['jsc_result'], 'required', 'on' => self::SCENARIO_ACADEMIC_INFO],
['ssc_equv_result', 'number', 'min' => 2.5, 'max' => 5],
['ssc_equv_result', 'validateSscEquvResult', 'skipOnError' => false, 'skipOnEmpty' => false],
['ssc_equv_group', 'validateSscEquvGroup', 'skipOnError' => false, 'skipOnEmpty' => false],
['trade_course_subject', 'validateTradeCourseSubject', 'skipOnError' => false, 'skipOnEmpty' => false],
['have_trade_course_experience', 'validateHaveTradeCourseExperience', 'skipOnError' => false, 'skipOnEmpty' => false],
['ssc_equv_is_biology_include', 'validateBiologyIsInclude', 'skipOnError' => false, 'skipOnEmpty' => false],
['ssc_equv_is_trade_course_complete', 'validateTradeCourseComplete', 'skipOnError' => false, 'skipOnEmpty' => false],
```
Four cross-field custom validators enforce the SSC/trade/biology block's internal consistency without a `Rule::in` style declarative approach (`CanEligibilityCheckInfo.php:123-168`):
```php
public function validateSscEquvResult($attribute, $params, $validator)
{
    if ($this->ssc_equv_group && empty($this->ssc_equv_result)) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
public function validateSscEquvGroup($attribute, $params, $validator)
{
    if ($this->ssc_equv_result && empty($this->ssc_equv_group)) {
        $this->addError($attribute,  $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
public function validateBiologyIsInclude($attribute, $params, $validator)
{
    if (($this->ssc_equv_result || $this->ssc_equv_group) && in_array($this->ssc_equv_group, [Constants::AC_GROUP_SCIENCE, Constants::AC_GROUP_VOCATIONAL])  && empty($this->$attribute)) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
public function validateTradeCourseComplete($attribute, $params, $validator)
{
    if (($this->ssc_equv_result || $this->ssc_equv_group) &&  empty($this->$attribute)) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
public function validateTradeCourseSubject($attribute, $params, $validator)
{
    if ($this->ssc_equv_is_trade_course_complete && $this->ssc_equv_is_trade_course_complete == Constants::YES   && empty($this->$attribute)) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
public function validateHaveTradeCourseExperience($attribute, $params, $validator)
{
    if ($this->ssc_equv_is_trade_course_complete && $this->ssc_equv_is_trade_course_complete == Constants::YES  && empty($this->$attribute)) {
        $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
    }
    return true;
}
```
Note: `validateTradeCourseComplete` targets attribute `ssc_equv_is_trade_course_complete` but its guard condition tests `ssc_equv_result`/`ssc_equv_group`, not the trade-course flag itself — effectively re-requiring the trade-course Yes/No radio to be answered any time an SSC group/result is present, independent of whether the candidate actually completed a trade course.

#### Fields

| Field | Input Type | Required? | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|
| `jsc_result` | Radio group (Pass/Fail), inline | Required (`on: SCENARIO_ACADEMIC_INFO`) | `required` | `StaticMethod::passFail()` — Pass(`YES`=1)/Failed(`NO`=2) | Directly matched against `Eligibility.jsc_result` in the Step-3 scoring query (exact equality, not a range). |
| `ssc_equv_group` | `<select>` | Conditionally required (via `validateSscEquvGroup` — required if `ssc_equv_result` is filled) | custom validator (see above) | `StaticMethod::academicGroupSsc()` — Science, Business(`'business studies'`), Humanities, Vocational, General(Madrasah), Muzabbid(Madrasah) | Toggles biology-inclusion block visibility (JS) when Science/Vocational; matched against `Eligibility.ssc_ac_group` (comma-exploded `in_array`) in scoring. |
| `ssc_equv_result` | `<input type="text">` | Conditionally required (via `validateSscEquvResult` — required if `ssc_equv_group` is filled) | `number, min:2.5, max:5` (only when non-empty) | free text (GPA) | Compared `>=` against `Eligibility.ssc_result` in scoring. |
| `ssc_equv_is_trade_course_complete` | Radio group (Yes/No), class `is_trade_course_complete_common_class` | Conditionally required via `validateTradeCourseComplete` whenever SSC group/result is present | custom validator | `StaticMethod::yesNo()` | Toggles the Trade-Course-Subject dropdown and the nested "trade course experience" radio (JS, `#diploma_course_visible`/`#trade_course_exp`). |
| `trade_course_subject` | `<select>` (shown inside `#diploma_course_visible`) | Conditionally required via `validateTradeCourseSubject` when trade-course-complete = Yes | custom validator | **DB-driven**: `Subjects::getAllActiveSubjectBySubjectType(subject_type: Constants::SUBJECT_TYPE_TRADE)` (`academic_info.php:173`, `common/models/Subjects.php:134-144`) — active subjects with `subject_type = SUBJECT_TYPE_TRADE (2)`, no `candidate_type` filter applied | Matched against `Eligibility.trade_course_subject` (comma-exploded `in_array`) only when the matched `Eligibility` row requires a trade course and forbids diploma (see §4 rule 6). |
| `have_trade_course_experience` | Radio group (Yes/No) inside `#trade_course_exp` | Conditionally required via `validateHaveTradeCourseExperience` when trade-course-complete = Yes | custom validator | `StaticMethod::yesNo()` | Compared against `Eligibility.is_required_trade_course_experience` in scoring (rule: candidate must answer `Yes` if the designation requires trade-course experience). |
| `ssc_equv_is_biology_include` | Radio group (Yes/No), shown only for Science/Vocational SSC group | Conditionally required via `validateBiologyIsInclude` when SSC group is Science or Vocational | custom validator | `StaticMethod::yesNo()` | Compared against `Eligibility.is_required_biology` — designation-level "must include biology" flag. |
| `hsc_equv_academic_type` | Radio group (HSC / Diploma) | Not required by any rule (no explicit rule for this attribute at all beyond the shared `integer` type-check) | none beyond `integer` | `StaticMethod::academicTypeHscDiploma()` — hard-coded to reuse `Constants::YES`(1)="HSC" / `Constants::NO`(2)="Diploma" | Click handler AJAX-fetches the group-option list from `/ajax/hsc-diploma-ac-group` and relabels the group/result fields (HSC vs Diploma wording); server-side, `hsc_equv_academic_type == Constants::NO (2)` means "Diploma" and gates the trade/diploma branch of the scoring engine (§4 rule 6). |
| `hsc_equv_group` | `<select>` | Not required by rules (populated client-side via AJAX, no server rule) | none beyond shared `integer`/`string,max:50` typing | AJAX endpoint `/ajax/hsc-diploma-ac-group` (out of this controller's scope — not `CheckEligibilityController`) | Matched against `Eligibility.hons_diploma_subject` (comma-exploded `in_array`) when the matched row requires diploma. |
| `hsc_equv_result` | `<input type="text">` | Not required by rules | `string, max:50` (shared rule; no `number` rule for this field, unlike `ssc_equv_result`) | free text | Compared `>=` against `Eligibility.diploma_result` when diploma is required. |

#### Conditional behavior (evidence-quoted, `academic_info.php:16-115`)

1. **SSC group → trade/biology block visibility, with auto radio-toggling:**
```js
$('#caneligibilitycheckinfo-ssc_equv_group').change(function() {
    let ac_group = $(this).val();
    let group_list = ['<?= Constants::AC_GROUP_SCIENCE; ?>', '<?= Constants::AC_GROUP_VOCATIONAL; ?>'];
    if (group_list.includes(ac_group.toString())) {
        $('#biology_include_trade_complete').show();
        if (ac_group.toString() == '<?= Constants::AC_GROUP_VOCATIONAL; ?>') {
            $('#i2').prop('checked', true).trigger('change');
            $('#is_trade_course_complete').hide();
            $('#trade_course_exp').show();
            $('#diploma_course_visible').show();
        } else {
            $('#is_trade_course_complete').show();
            $('#i2, #i3').prop('checked', false).trigger('change');
            $('#trade_course_exp').hide();
            $('#diploma_course_visible').hide();
        }
    } else {
        $('#is_trade_course_complete').show();
        $('#biology_include_trade_complete').hide()
        $('#trade_course_exp').hide();
        $('#diploma_course_visible').hide();
        $('#i2,#i3').prop('checked', false).trigger('change');
    }
})
```
Selecting **Vocational** auto-checks the "trade course complete = Yes" radio (`#i2`) and force-shows the trade-subject/experience blocks — the user cannot answer "No" to trade-course-completion while SSC group = Vocational without first changing the group.
2. **Trade-course-complete toggle:**
```js
$(".is_trade_course_complete_common_class").click(function() {
    var trade_course_yes = $(".is_trade_course_complete_common_class:checked").val();
    if (trade_course_yes == '<?= Constants::YES ?>') {
        $('#diploma_course_visible').show();
        $('#trade_course_exp').show();
    } else {
        $('#diploma_course_visible').hide();
        $('#trade_course_exp').hide();
        $('#i4, #i5').prop('checked', false).trigger('change');
        $('#caneligibilitycheckinfo-trade_course_subject').val('');
    }
})
```
3. **HSC/Diploma type AJAX relabel:**
```js
$(".hsc_eqv_ac_type_common_class").click(function() {
    var ac_type = $(".hsc_eqv_ac_type_common_class:checked").val();
    if (ac_type) {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/ajax/hsc-diploma-ac-group' ?>',
            type: 'POST', dataType: 'json', data: { ac_type: ac_type },
            success: function(data) {
                let select_text = '<?= $model->getAttributeLabel('hsc_equv_group') ?>';
                let hsc_diploma_result = 'HSC Result';
                if ($(".hsc_eqv_ac_type_common_class:checked").val() == 2) {
                    select_text = 'Diploma Course';
                    hsc_diploma_result = 'Diploma Result';
                }
                $('#caneligibilitycheckinfo-hsc_equv_result').attr('placeholder', hsc_diploma_result)
                let group_list = ['<option value>Select ' + select_text + ' </option>'];
                if (data) { for (const [key, value] of Object.entries(data)) group_list.push('<option value=' + key + '>' + value + '</option>'); }
                $('#caneligibilitycheckinfo-hsc_equv_group').html(group_list.join(''));
            }
        });
    }
});
```

---

## 4. Backend Business Logic

### 4.1 Step 1 → Step 2 handoff (`actionIndex()`, `CheckEligibilityController.php:36-84`)

```php
$model = new CanEligibilityCheckInfo();
$model->apply_department_type = Constants::CANDIDATE_SAILOR;
$model->scenario = $model::SCENARIO_PERSONAL_INFO;
...
if ($this->request->isPost) {
    if ($model->load($this->request->post()) && $model->validate()) {
        // change height to CM
        $model->height = StaticMethod::heightChangeFeetInchToCM(inch: $model->height_inch, feet: $model->height_feet);
        $model->dob = date('Y-m-d', strtotime($model->dob));
        if ($model->save()) {
            return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/academic-info", 'slug' => StaticMethod::encryptPk($model->id)]));
        }
    }
}
```
`heightChangeFeetInchToCM()` (`StaticMethod.php:472-479`):
```php
public static function heightChangeFeetInchToCM(int $feet = 0, int $inch = 0)
{
    $feet_to_inch = 0;
    if ($feet > 0)
        $feet_to_inch = $feet * Constants::FEET_TO_INCH_MULTI_BY;   // 12
    $total_inch = $feet_to_inch + $inch;
    return strval($total_inch * Constants::INCH_TO_CM_MULTI_BY);    // 2.54
}
```

### 4.2 Step 2 → Step 3 handoff (`actionAcademicInfo()`, `CheckEligibilityController.php:91-124`)

No field normalization happens here (unlike the officer wizard's O-Level/A-Level copy-down logic) — the model is loaded, validated against `SCENARIO_ACADEMIC_INFO`, and saved as-is:
```php
if ($this->request->isPost) {
    if ($model->load($this->request->post()) && $model->validate()) {
        if ($model->save()) {
            return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/eligible-department", 'slug' => StaticMethod::encryptPk($model->id)]));
        }
        echo '<pre>';
        print_r($model->attributes);
        echo '</pre>';
        die();
    }
}
```

### 4.3 `actionEligibleDepartment()` scoring engine — key rules (`CheckEligibilityController.php:130-367`)

1. **Working data re-fetch is a fixed column subset, not the full model, and skips `findModel()`/scenario entirely:**
```php
// CheckEligibilityController.php:136-159
$model = CanEligibilityCheckInfo::find()
    ->select(['id', 'candidate_type', 'gender', 'marital_status', 'height', 'eye_status', 'jsc_result',
        'ssc_equv_result', 'ssc_equv_group', 'ssc_equv_is_biology_include', 'ssc_equv_is_trade_course_complete',
        'trade_course_subject', 'hsc_equv_academic_type', 'hsc_equv_result', 'hsc_equv_group', 'dob', 'district',
        'have_trade_course_experience'])
    ->where(['id' => StaticMethod::decryptPk($slug)])->asArray()->one();
```
Notably **`chest_normal`, `chest_expanded`, and `nationality` are never selected here** — confirming those two Step-1 fields are collected but structurally unused by the scoring engine (`Eligibility` has chest columns but nothing in this query path ever reads or compares them).

2. **Active-batch age window construction** (lines 173-208): queries `SailorBatchs` for rows that are `status=active`, `is_active_batch=active`, and whose `circular_close_date >= now` and `circular_start_date <= now` (`SailorBatchs` scope query, `common/models/scopeQuery/SailorBatchs.php:14-35`). If no batch is currently open, the age window falls back to today's date (`$batch_age_count[] = date('Y-m-d');`, line 186) — so eligibility can still be evaluated against an implicit "as of today" age even with zero open batches (though the later district/config step will then report "no running batch"). For each batch date, age is computed via:
```php
// StaticMethod.php:269-287
public static function getDifferenceBetweenTwoDateYearMonth(string $maxDate = null, string $minDate = null)
{
    if ($maxDate && $minDate) {
        $toDate = Carbon::parse($minDate);
        $fromDate = Carbon::parse($maxDate);
        $age = Carbon::createFromDate($toDate)->diff($fromDate)->format('%y.%M.%D');
        return $age;
    }
    return '';
}
```
and a dynamic `OR`-of-`AND` where clause is built per batch — Departmental candidates (`candidate_type == ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL`, 3) are compared against `dept_can_max_age` instead of `max_age`:
```php
// CheckEligibilityController.php:199-207
$max_age  = "max_age>='" . $age_year_month . "'";
if ($model['candidate_type'] == Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL)
    $max_age  = "dept_can_max_age>='" . $age_year_month . "'";
$dynamic_between[] = ['and', "min_age<='" . $age_year_month . "'", $max_age];
```
Since every row this wizard actually creates has `candidate_type = GENERAL (1)` (§3, Form 1), the `dept_can_max_age` branch is currently dead in practice for wizard-originated rows — it only fires for `CanEligibilityCheckInfo` rows created elsewhere (e.g. admin-entered), because the public UI never lets `candidate_type` reach `3`.

3. **`Eligibility` base query** (lines 217-235) — active rows matching exact `jsc_result`, gender REGEXP, marital_status REGEXP, a gender-split height comparison, and the dynamic age window:
```php
$eligibility_model = Eligibility::find()
    ->select([...])
    ->where(['status' => Constants::STATUS_ACTIVE])
    ->andWhere(['jsc_result' => $model['jsc_result']])
    ->andWhere(['REGEXP', 'gender', $model['gender']])
    ->andWhere(['REGEXP', 'marital_status', $model['marital_status']]);
if ($model['gender'] == Constants::GENDER_MALE)
    $eligibility_model->andWhere(['<=', 'height_male', $model['height']]);
else
    $eligibility_model->andWhere(['<=', 'height_female', $model['height']]);
$eligibility_model->andWhere($dynamic_between);
$eligibility_data_model = $eligibility_model->asArray()->all();
```
Unlike the officer wizard, **there is no height-bucket rounding quirk** (no `5.10`/`5.11` collapse) here — height is compared as raw centimeters.

4. **Post-query PHP filtering** (lines 252-313) loops each candidate `Eligibility` row and, for rows with **no** `ssc_result`/`ssc_ac_group` set, auto-passes them ("for topass" — presumably no academic requirement, e.g. a designation open to any SSC outcome):
```php
foreach ($eligibility_data_model as $k => $eligible_array) {
    if (empty($eligible_array['ssc_result']) || empty($eligible_array['ssc_ac_group'])) {  /// for topass
        $eligible_ids[] = $eligible_array['id'];
    } else {
        if (!empty($model['ssc_equv_result']) && $model['ssc_equv_result'] >= $eligible_array['ssc_result']
            && !empty($model['ssc_equv_group']) && in_array($model['ssc_equv_group'], explode(',', $eligible_array['ssc_ac_group']))
        ) {
            if ($eligible_array['is_required_biology'] == Constants::YES && $model['ssc_equv_is_biology_include'] != Constants::YES) {
                continue;
            }
            if ($eligible_array['is_required_trade_course_experience'] == Constants::YES && !empty($model['have_trade_course_experience']) && $model['have_trade_course_experience'] != Constants::YES) {
                continue;
            }
            if ($eligible_array['is_allow_trade_course'] == Constants::YES && $eligible_array['is_allow_diploma'] == Constants::NO) {
                // must have completed trade course, and its subject must match
                if ($model['ssc_equv_is_trade_course_complete'] == Constants::YES
                    && in_array($model['trade_course_subject'], explode(',', $eligible_array['trade_course_subject']))) {
                    $eligible_ids[] = $eligible_array['id'];
                }
                continue;
            } else if ($eligible_array['is_allow_diploma'] == Constants::YES && $eligible_array['is_allow_trade_course'] == Constants::NO) {
                // must have diploma (not HSC), result high enough, subject matches
                if (!empty($model['hsc_equv_academic_type']) && $model['hsc_equv_academic_type'] == Constants::NO
                    && $model['hsc_equv_result'] >= $eligible_array['diploma_result']
                    && !empty($eligible_array['hons_diploma_subject'])
                    && in_array($model['hsc_equv_group'], explode(',', $eligible_array['hons_diploma_subject']))) {
                    $eligible_ids[] = $eligible_array['id'];
                }
                continue;
            } else if ($eligible_array['is_allow_trade_course'] == Constants::NO && $eligible_array['is_allow_diploma'] == Constants::NO) {
                // neither trade course nor diploma required — pass on SSC alone
                $eligible_ids[] = $eligible_array['id'];
                continue;
            }
        }
    }
}
```
Four mutually exclusive shapes per matched `Eligibility` row: (a) no SSC requirement at all → auto-pass; (b) SSC passes AND designation requires trade-course-only → also requires `ssc_equv_is_trade_course_complete == Yes` and subject membership; (c) SSC passes AND designation requires diploma-only → also requires `hsc_equv_academic_type == Diploma(2)`, `hsc_equv_result >= diploma_result`, and HSC/diploma group membership in `hons_diploma_subject`; (d) SSC passes AND neither trade nor diploma required → pass on SSC alone. Note: a designation configured with **both** `is_allow_trade_course = Yes` **and** `is_allow_diploma = Yes` simultaneously falls through all three `else if` branches unmatched (only the trade-XOR-diploma and neither-required shapes are handled) — such a row can never be added to `$eligible_ids` from this loop, an edge case the code does not explicitly account for.

5. **Batch-type and district/gender filtering** (lines 319-352): eligible `Eligibility` ids are re-queried filtered to `candidate_type IN (<active batches' candidate types>)`, then cross-referenced against `SailorBatchConfiguration` rows for the active batch ids where the configuration's `gender` REGEXP-matches and `district_slug` REGEXP-matches the candidate's district (or is blank, meaning "all districts"):
```php
// CheckEligibilityController.php:319-352
$eligibility_data_model = Eligibility::find()
    ->select(['id', 'candidate_type', 'candidate_designation'])
    ->where(['in', 'id', $eligible_ids])
    ->andFilterWhere(['in', 'candidate_type', $allow_batch_candidate_type])
    ->asArray()->all();

if ($active_batch_ids) {
    $config = SailorBatchConfiguration::find()
        ->select(['id', 'candidate_designation', 'candidate_type', 'batch_id', 'center_id'])
        ->isActive()
        ->andFilterWhere(['in', 'batch_id', $active_batch_ids])
        ->andWhere(['REGEXP', 'gender', $model['gender']])
        ->andWhere(['or', ['REGEXP', 'district_slug', "(^|,)" . $model['district'] . "(,|$)"], ['=', 'district_slug', '']])
        ->asArray()->all();
    foreach ($config as $k => $value_config) {
        $explode_desig = explode(',', $value_config['candidate_designation']);
        if (!array_key_exists($value_config['candidate_type'], $config_desig_candidate_type_wise))
            $config_desig_candidate_type_wise[$value_config['candidate_type']] = $explode_desig;
        else
            $config_desig_candidate_type_wise[$value_config['candidate_type']] = array_unique(array_merge($config_desig_candidate_type_wise[$value_config['candidate_type']], $explode_desig));
    }
}
```
The final render (`eligible_department.php`) cross-checks each scored `Eligibility` row's `candidate_designation` against `$config_desig_candidate_type_wise[$candidate_type]` — only designations both scoring-eligible **and** open for the candidate's district/gender in an active batch's configuration are shown with an "Apply" button; everything else renders "Your district is not allow for this department or batch setting missing." (`eligible_department.php:139`), and if there are no active batches at all, every row instead renders "Sorry. There is no running batch" (line 141). The final eligible designation-id list is written to `$_SESSION['eligible_department']` (`eligible_department.php:150`) for `actionApplyDepartment()` to authorize against.

This entire Step 3 computation is **synchronous, server-rendered PHP** — no AJAX involved in the scoring itself; only the Step-3 "Details" modal (`get-description`) is AJAX.

### 4.4 `actionApplyDepartment()` — apply hand-off (`CheckEligibilityController.php:372-457`)

```php
if ($slug &&  $adpt) {
    $session = Yii::$app->session;
    if ($session->has('eligible_department')) {
        $adpt = StaticMethod::decryptPk($adpt);
        $eligible_department = $session->get('eligible_department');
        if (in_array($adpt, $eligible_department)) {
            $model = $this->findModel($slug);
            $model->eligible_dept = implode(',', $eligible_department);
            $model->apply_department = $adpt;
            $model->apply_department_type = CanDesignation::find()->select(['id', 'candidate_type'])->where(['id' => $adpt])->asArray()->one()['candidate_type'];

            $canTypeSailorOrDeSailor = 'sailor';
            if ($model->apply_department_type && in_array($model->apply_department_type, [Constants::CANDIDATE_DE_SAILOR, Constants::CANDIDATE_DE_SAILOR_DOCKYARD]))
                $canTypeSailorOrDeSailor = 'de_sailor';

            $batch_and_config_id = SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(applyDept: $model->apply_department, elgilbe_district: $model->district, gender: $model->gender);

            if ($batch_and_config_id && $model->save()) {
                if (Yii::$app->user->getIsGuest()) {
                    return $this->redirect(Yii::$app->urlManager->createUrl(["candidate/sign-up", 'ceci' => StaticMethod::encryptPk($model->id)]));
                } else {
                    $check_already_applyed = ($canTypeSailorOrDeSailor == 'sailor')
                        ? Sailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id'])
                        : DeSailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);
                    if ($check_already_applyed === 0) {
                        $sailor = ($canTypeSailorOrDeSailor == 'sailor') ? new Sailors() : new DeSailors();
                        $sailor->candidate_type = $model->apply_department_type;
                        $sailor->eligibility_info_id = $model->id;
                        $sailor->app_unique_id = date('ymdH') . time();
                        $sailor->candidate_designation = $model->apply_department;
                        $sailor->eligible_district = $model->district;
                        $sailor->center_id = $batch_and_config_id['center_id'];
                        $sailor->batch_id = $batch_and_config_id['batch_id'];
                        if ($sailor->save()) {
                            return $this->redirect(/* sailor-candidate/payment or de-sailor/payment */);
                        } else {
                            echo '<pre>'; print_r($sailor); echo '</pre>'; die();
                        }
                    } else {
                        Yii::$app->session->setFlash('error', "Sorry you already applied once this batch");
                        return $this->redirect([...'check-eligibility/eligible-department', 'slug' => $slug]);
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', "Please try again after sometime.");
                return $this->redirect([...'check-eligibility/eligible-department', 'slug' => $slug]);
            }
        } else {
            Yii::$app->session->setFlash('error', "Sorry.You are not allow to apply your selected department");
            return $this->redirect([...'check-eligibility/eligible-department', 'slug' => $slug]);
        }
    }
    return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility"]));
}
return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility"]));
```
Two authorization layers before anything is written: the session must actually contain `eligible_department` (i.e. the candidate must have just come from Step 3, not deep-linked), and the decrypted `adpt` designation id must be a member of that session array. `apply_department_type` is looked up fresh from `CanDesignation.candidate_type` (not trusted from the client) and used to route between `Sailors` and `DeSailors` application tables. A guest is bounced to `candidate/sign-up?ceci=<encrypted CanEligibilityCheckInfo id>`; a logged-in candidate gets a fresh application row created immediately (guarded by a one-application-per-batch check via `Sailors::numberOfApplication()`/`DeSailors::numberOfApplication()`) and is sent straight to payment.

### 4.5 Sign-up hand-off — `CandidateController::haveCeci()` (`frontend/controllers/CandidateController.php:229-303`)

Called from `actionSignUp()` (`CandidateController.php:86-101`) once a guest who arrived via `?ceci=<encrypted id>` finishes registering and is logged in. `haveCeci()` re-derives everything from the `CanEligibilityCheckInfo` row rather than trusting anything from the session, and is idempotent against duplicate use of the same `ceci`:
```php
// CandidateController.php:229-303 (abridged, full logic already quoted in §4.4-equivalent flow above for the guest path's counterpart)
$can_eligibility_info = CanEligibilityCheckInfo::find()->where(['id' => StaticMethod::decryptPk($ceci)])->one();
if ($can_eligibility_info['apply_department_type'] && in_array($can_eligibility_info['apply_department_type'], [Constants::CANDIDATE_DE_SAILOR, Constants::CANDIDATE_DE_SAILOR_DOCKYARD]))
    $return['sailor_or_de_sailor'] = 'de_sailor';

$no_application = ($return['sailor_or_de_sailor'] == 'sailor')
    ? Sailors::find()->where(['eligibility_info_id' => StaticMethod::decryptPk($ceci)])->count()
    : DeSailors::find()->where(['eligibility_info_id' => StaticMethod::decryptPk($ceci)])->count();

if ($no_application > 0) {
    $return['page'] = 'go-home';  // this ceci was already used to create an application — don't create a duplicate
} else {
    $batch_and_config_id = SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(applyDept: $can_eligibility_info->apply_department, elgilbe_district: $can_eligibility_info->district, gender: $can_eligibility_info->gender);
    $check_already_applyed = ($return['sailor_or_de_sailor'] == 'sailor')
        ? Sailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id'])
        : DeSailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);
    if ($check_already_applyed === 0) {
        $sailor = ($return['sailor_or_de_sailor'] == 'sailor') ? new Sailors() : new DeSailors();
        if ($return['sailor_or_de_sailor'] == 'de_sailor')
            $sailor->trade_course_experience = $can_eligibility_info['have_trade_course_experience'];
        $sailor->candidate_type = $can_eligibility_info['apply_department_type'];
        $sailor->eligibility_info_id = $can_eligibility_info->id;
        $sailor->app_unique_id = date('ymdH') . time();
        $sailor->candidate_designation = $can_eligibility_info->apply_department;
        $sailor->eligible_district = $can_eligibility_info->district;
        $sailor->center_id = $batch_and_config_id['center_id'];
        $sailor->batch_id = $batch_and_config_id['batch_id'];
        if ($can_eligibility_info->candidate_type == Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA) {
            $sailor->is_child_of_naval_officer = Constants::YES;
            $sailor->naval_office_no = $can_eligibility_info->p_o_no;
            $sailor->naval_rank =  $can_eligibility_info->rank;
        } else if ($can_eligibility_info->candidate_type == Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL) {
            $sailor->is_departmental_candidate = Constants::YES;
            $sailor->naval_office_no = $can_eligibility_info->p_o_no;
            $sailor->naval_rank =  $can_eligibility_info->rank;
        }
        if ($sailor->save()) { $return['page'] = 'payment'; $return['id'] = $sailor->id; }
    } else {
        $return['page'] = 'application-list';
    }
}
return $return;
```
The `ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA`/`DEPARTMENTAL` branches that populate `naval_office_no`/`naval_rank` from `p_o_no`/`rank` are, per §3, unreachable via the current public wizard UI (which always saves `candidate_type = GENERAL`), but remain live code paths for any `CanEligibilityCheckInfo` row created with those types by another entry point (e.g. backend/admin tooling). `actionSignUp()` then branches on `$return['page']`: `'payment'` redirects to `sailor-candidate/payment` or `de-sailor/payment`; `'application-list'` or `'go-home'` (the latter also flashes "Please check eligibility again for apply") both redirect to `/my-application`.

---

## 5. Debug Leftovers (evidence-quoted)

Three production debug artifacts remain live in `CheckEligibilityController.php`:

1. **`actionAcademicInfo()` — `print_r`/`die()` on save failure** (lines 111-114), reached whenever `$model->validate()` passes but `$model->save()` fails (e.g. a DB-level constraint violation not caught by validation):
```php
echo '<pre>';
print_r($model->attributes);
echo '</pre>';
die();
```
This halts the request with a raw `print_r` dump instead of re-rendering the form with an error — a public visitor hitting this path sees a blank debug page, and the request never completes gracefully.

2. **`actionApplyDepartment()` — `print_r`/`die()` on Sailors/DeSailors save failure** (lines 435-438), reached when the batch/config lookup and `CanEligibilityCheckInfo::save()` both succeed but creating the `Sailors`/`DeSailors` application row fails:
```php
echo '<pre>';
print_r($sailor);
echo '</pre>';
die();
```
Same failure mode as above — dumps the entire (unsaved) ActiveRecord object, including any loaded relations, and kills the response.

3. **`actionGetDescription()` — unreachable `exit()` after `return`** (line 469):
```php
public function actionGetDescription()
{
    if (Yii::$app->request->isAjax) {
        $data = Yii::$app->request->post();
        $can_desig =  CanDesignation::find()->select(['id', 'description', 'name_en'])->where(['id' => $data['id']])->asArray()->one();
        return json_encode($can_desig);
        exit();
    }
}
```
`exit();` on the line after `return` never executes — dead code, harmless but confirms the method was hand-patched (likely a leftover from an earlier `echo ...; exit();` version converted to `return` without removing the trailing `exit()`).

---

## 6. Modal Audit (wizard-wide summary)

| Modal | Page(s) | Trigger | Fields | Validation | Buttons | Backend Call | Success/Fail Flow |
|---|---|---|---|---|---|---|---|
| `#staticBackdrop-eligibility` | `personal_info.php` (444-460) **and** `academic_info.php` (389-406) — identical markup duplicated on both pages | `<button data-bs-toggle="modal" data-bs-target="#staticBackdrop-eligibility">Check Eligibility</button>` in the "Confused about Your Eligibility?" band | None — body is a single static `<img>` | None | Single header close button (`data-bs-dismiss="modal"`) | None | None — purely informational; closing just dismisses the modal. |
| `#exampleModal` | `eligible_department.php` (191-202) | `.show_modal` click (per-row "Details" text) | None (read-only display) | None | Header close button only | `POST check-eligibility/get-description` (AJAX, CSRF-exempt) | On success, injects the fetched designation `name_en`/`description` into the modal body/title and shows it via `.modal('show')`; on AJAX failure there is no explicit error handler — the modal simply does not open. |

No modal was found in `academic_info.php` beyond the duplicated promo modal, and none in the `apply-department`/`get-description` flow itself (those are plain redirects/JSON responses, not modal-driven).
