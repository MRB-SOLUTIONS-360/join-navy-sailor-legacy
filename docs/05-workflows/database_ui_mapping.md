# Database ↔ UI Relationship Mapping (Join Navy Sailor)

**Generated:** 2026-08-20
**Framework:** Yii2 2.0 advanced template (`yii\db\ActiveRecord`) — **not** Eloquent/Laravel. There is no `$fillable` allowlist; every table column becomes a public AR property automatically on `find()`/`new`, and input safety is governed entirely by each model's `rules()` (scenario-gated via `'on' => ...`) plus whatever `ActiveForm::field($model, 'attribute')` / `Html::activeXxx($model, 'attribute')` calls a given view happens to render. A field can be `rules()`-safe yet never appear on any view (dead/unreachable), or appear on a view yet be validated by a scenario the view never triggers.
**Scope:** `Sailors`, `DeSailors` (the two main candidate-application tables — done first/most thoroughly, covering every wizard step), `User` (frontend candidate account + backend admin account), `SailorBatchs`/`SailorBatchConfiguration`/`SailorBatchConfigurationExamDate`, `SailorCenters`/`SailorCentDistMapping`, `Eligibility`, `CanEligibilityCheckInfo`, `Districts`/`Unions`/`Upozilas`.

**Method:** Every `rules()` array below is read directly from `common/models/*.php` — quoted verbatim for `Sailors` (`common/models/Sailors.php` lines 196–486, read in full for this document) and `DeSailors` (already quoted verbatim in `docs/00-inventories/model_inventory.md` lines 112–161), summarized-with-citation for the rest. Every "View Field" is a `name="ModelName[attribute]"` HTML attribute (Yii2's `ActiveForm`/`Html::activeXxx` naming convention) found by reading the actual view file cited, plus the paired controller to check for any POST-field-to-attribute remapping. Nothing here is inferred without a citation back to `docs/00-inventories/model_inventory.md` or a source file path.

**Critical structural finding, applies to every table below except `user`:** per `docs/00-inventories/model_inventory.md` §"Cross-cutting observations" and its migration-file reference section, this repo contains exactly **2** migration files (`console/migrations/`), and only `{{%user}}` is created by them — and even `user` has drifted (11 of ~20 columns the app actually uses are absent from both migrations; see §3). Every other table referenced below — `sailors`, `de_sailors`, `sailor_batchs`, `sailor_batch_configuration`, `sailor_batch_configuration_exam_date`, `sailor_centers`, `sailor_cent_dist_mapping`, `districts`, `unions`, `upozilas`, `eligibility`, `can_eligibility_check_info` — **exists only in a live database dump not present in this repository.** Column names/types/defaults below are inferred entirely from `rules()`, `attributeLabels()`, and doc-comments; there is no migration to cite for column existence, so every field in every table below should be read as **"NOT IN MIGRATIONS"** by default — this is called out once here rather than per-row (unlike the officer-legacy app, where migrations exist and only a handful of fields diverge). Anyone rebuilding schema from this repo alone must treat every `rules()` block below as the closest thing to a spec.

---

## 1. Sailors — Personal Info step (largest/central entity, done first)

- **DB table:** `sailors` (`{{%sailors}}`) — no migration, live DB only.
- **Model:** `common/models/Sailors.php` (1315 lines — the largest model in the app). `rules()` at lines 196–486 (read in full for this document).
- **Scenario constants** (`'on' => ...` targets, no `scenarios()` override): `PAYMENT = 'select_payment_type'`, `ACADEMIC_INFO_JSC = 'academic_info_jsc'`, `ACADEMIC_INFO_JSC_SSC = 'academic_info_jsc_ssc'`, `PERSONAL_INFO = 'personal_information'`, `PERSONAL_INFO_WITH_IMAGE = 'personal_information_with_image'` (chosen by controller based on `file_exists($prevImage)`).
- **Public non-column properties** (declared on the class, NOT AR-backed columns, yet appear in `rules()`'s `safe` list): `$agree_payment_terms`, `$exam_center_name`, `$academic_info_already_used_in_ssc`, `$academic_info_already_used_in_jsc`, `$list_custom_filter`, `$encryption_fields_for_personal_info` (array constant, not a form field).
- **UI:** `frontend/views/sailor-candidate/personal_info.php`
- **Controller:** `frontend/controllers/SailorCandidateController.php`

| View Field (`Sailors[...]`) | DB Column | Validation Rule (from `rules()`) | Notes |
|---|---|---|---|
| `[district]` (dropDownList) | `district` | `string, max 150` (line 420) | Distinct from `current_district`/`permanent_district` — used for eligible-district/center-assignment logic elsewhere in the app. |
| `[gender]` (dropDownList) | `gender` | `integer`; `genderValidation` custom validator on `PERSONAL_INFO` | — |
| `[exam_center_name]` (readonly text) | *(none — public property, not a column)* | `personalInformationValidate`; also listed `safe` (line 414) | Server pre-fills; not a real DB column. |
| `[name]` | `name` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[name_bangla]` | `name_bangla` | `personalInformationValidate`; `banglaInputCharacterValidation`; `string max 250` | — |
| `[permanent_phone]` | `permanent_phone` | `personalInformationValidate`; `onlyNumberInputValidation`; `phoneNoValidation`; `permanentPhoneUniqueCheck`; `string max 150` | Uniqueness enforced app-side, not via DB constraint. |
| `[father_name]` | `father_name` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[father_name_bangla]` | `father_name_bangla` | `personalInformationValidate`; `banglaInputCharacterValidation`; `string max 250` | — |
| `[father_phone]` | `father_phone` | `personalInformationValidate`; `onlyNumberInputValidation`; `phoneNoValidation`; `string max 100` | Listed in `$encryption_fields_for_personal_info` (encrypted at rest per that array, though no explicit encrypt call was found in the excerpt read). |
| `[father_nid]` | `father_nid` | `onlyNumberInputValidation`; `string max 20`, custom Bangla `tooLong` message | — |
| `[father_occupation]` | `father_occupation` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 30`, custom Bangla `tooLong` | — |
| `[mother_name]` | `mother_name` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[mother_name_bangla]` | `mother_name_bangla` | `personalInformationValidate`; `banglaInputCharacterValidation`; `string max 250` | — |
| `[mother_phone]` | `mother_phone` | `personalInformationValidate`; `onlyNumberInputValidation`; `phoneNoValidation`; `string max 100` | — |
| `[mother_occupation]` | `mother_occupation` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 15`, custom Bangla `tooLong` | Cap is stricter than father's (30) — real, source-confirmed asymmetry, not a typo introduced here. |
| `[current_district]` | `current_district` | `personalInformationValidate`; `string max 150` | — |
| `[current_thana]` (AJAX-populated dropdown) | `current_thana` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 20`, custom `tooLong` | — |
| `[current_union]` | `current_union` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 25`, custom `tooLong` | — |
| `[current_post_office]` | `current_post_office` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 20`, custom `tooLong` | — |
| `[current_village]` | `current_village` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 25`, custom `tooLong` | — |
| `[current_word_no]` | `current_word_no` | `onlyInputEnglishCharacterValidation`; `string max 15`, custom `tooLong` | Not in `personalInformationValidate`'s field list (that custom validator's list omits it) despite being on the form. |
| `[current_post_code]` | `current_post_code` | `personalInformationValidate`; `onlyNumberInputValidation`; `string max 10`, custom `tooLong` | — |
| — (rendered field commented out) | `current_phone` | `phoneNoValidation` (rule stays **active**, line 376) — but explicitly commented out of `personalInformationValidate`'s field list (line 224) and `onlyNumberInputValidation`'s list (line 339) | **Dead field**: the `<input>` is commented out of `personal_info.php` entirely, yet a phone-format validator still fires on it in `rules()` (harmless since it's always empty) and it's still covered by the `max 150` string rule (line 420). No UI path can ever populate it. |
| `[permanent_district]` | `permanent_district` | `personalInformationValidate`; `string max 150` | — |
| `[permanent_thana]` | `permanent_thana` | `personalInformationValidate`; `onlyInputEnglishCharacterValidation`; `string max 20` — shares the **same** `tooLong` message key as `current_thana` (line 431 covers both) | — |
| `[permanent_union]` | `permanent_union` | same pattern as `current_union`, `max 25` | — |
| `[permanent_post_office]` | `permanent_post_office` | same pattern as `current_post_office`, `max 20` | — |
| `[permanent_village]` | `permanent_village` | same pattern as `current_village`, `max 25` | — |
| `[permanent_word_no]` | `permanent_word_no` | `onlyInputEnglishCharacterValidation`; `max 15` | — |
| `[permanent_post_code]` | `permanent_post_code` | `personalInformationValidate`; `onlyNumberInputValidation`; `max 10` — **`tooLong` message bug**: line 432 sources its message text from `attributeLabelBangla()['current_thana']` instead of a post-code label — copy/paste leftover, not fixed up. | — |
| `[permanent_phone]` (real, active) | `permanent_phone` | see row above (this is the same field as the one under "Personal") | — |
| `[guardian_name]` (textarea) | `guardian_name` | `onlyInputEnglishCharacterValidation`; `string max 45`, custom `tooLong` | — |
| `[guardian_phone]` | `guardian_phone` | `phoneNoValidation`; `string max 100` | — |
| `[guardian_relation]` | `guardian_relation` | `onlyInputEnglishCharacterValidation`; `string max 20`, custom `tooLong` | — |
| `[guardian_occupation]` | `guardian_occupation` | `onlyInputEnglishCharacterValidation`; `string max 20`, custom `tooLong` | — |
| `[guardian_address]` (textarea) | `guardian_address` | `onlyInputEnglishCharacterValidation`; `string max 100`, custom `tooLong` | — |
| `[dob]` | `dob` | `personalInformationValidate`; `ageValidation` custom validator on `PERSONAL_INFO`/`PERSONAL_INFO_WITH_IMAGE`; also `safe` | — |
| — (commented out; server-computed only) | `age_according_to_circular` | `personalInformationValidate`'s field list includes it | Present in `rules()` but the `<input>` is commented out of the view — set server-side from `dob` + batch circular date, not user-entered. |
| `[religion]` | `religion` | `personalInformationValidate`; `integer` | — |
| `[marital_status]` | `marital_status` | `personalInformationValidate`; `maritalStatusValidation` custom; `integer` | — |
| `[nationality]` | `nationality` | `personalInformationValidate`; `string max 150` | — |
| **Academic block (re-displayed/editable on this step; distinct render from `academic_info.php`)** | | | |
| `[jsc_institute_name]` | `jsc_institute_name` | `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[jsc_reg_no]` | `jsc_reg_no` | `onlyNumberInputValidationWithBackslash` (decimal/backslash-tolerant number validator) | — |
| `[jsc_passing_year]` | `jsc_passing_year` | `onlyNumberInputValidation`; `string max 100` | — |
| `[jsc_gpa]` | `jsc_gpa` | `onlyInputEnglishCharacterValidation`; `string max 50` | — |
| `[ssc_institute]` (editable here — unlike `academic_info.php` where it's TeleTalk-filled) | `ssc_institute` | `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[ssc_group]` (hiddenInput once set, uppercased display) | `ssc_group` | `onlyInputEnglishCharacterValidation`; `string max 50` | — |
| `[ssc_edu_board]` (hiddenInput once set) | `ssc_edu_board` | `onlyInputEnglishCharacterValidation`; `string max 50` | — |
| `[ssc_reg_no]` (hiddenInput once set) | `ssc_reg_no` | `onlyNumberInputValidation`; `string max 50` | — |
| `[ssc_roll_no]` (hiddenInput once set) | `ssc_roll_no` | `onlyNumberInputValidation`; `string max 20` (own dedicated rule, line 448) | — |
| `[ssc_passing_year]` (hiddenInput once set) | `ssc_passing_year` | `onlyNumberInputValidation`; `string max 50` | — |
| `[ssc_gpa]` (hiddenInput once set) | `ssc_gpa` | `onlyDecimalNumberInputValidation`; `string max 50` | — |
| `[ssc_additional_subject]` | `ssc_additional_subject` | `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| `[hsc_dip_institute]` | `hsc_dip_institute` | `string max 250` (no character-class validator — not in the `onlyInputEnglishCharacterValidation` field list, an inconsistency vs `ssc_institute`) | — |
| `[hsc_dip_group]` (conditional textInput/hiddenInput) | `hsc_dip_group` | `onlyInputEnglishCharacterValidation`; `string max 50` | — |
| `[hsc_dip_board]` | `hsc_dip_board` | `onlyInputEnglishCharacterValidation`; `string max 50` | — |
| `[hsc_dip_reg_no]` | `hsc_dip_reg_no` | `onlyNumberInputValidation`; `string max 50` | — |
| `[hsc_dip_roll_no]` | `hsc_dip_roll_no` | `onlyNumberInputValidation`; `string max 50` | — |
| `[hsc_dip_passing_year]` | `hsc_dip_passing_year` | `onlyNumberInputValidation`; `string max 50` | — |
| `[hsc_dip_gpa]` | `hsc_dip_gpa` | `onlyDecimalNumberInputValidation`; `string max 50` | — |
| `[hsc_dip_additional_subject]` | `hsc_dip_additional_subject` | `onlyInputEnglishCharacterValidation`; `string max 250` | — |
| **Experience block — only "one" and "two" are rendered anywhere** | | | |
| `[experience_one_institute]` / `[experience_two_institute]` | `experience_one_institute` / `experience_two_institute` | `onlyInputEnglishCharacterValidation`; `string max 40`, custom `tooLong` | — |
| `[experience_one_subject]` / `[experience_two_subject]` | `experience_one_subject` / `experience_two_subject` | `onlyInputEnglishCharacterValidation`; `string max 30`, custom `tooLong` | — |
| `[experience_one_year]` / `[experience_two_year]` | `experience_one_year` / `experience_two_year` | `onlyNumberInputValidation`; `string max 4`, custom `tooLong` | — |
| `[experience_one_cert_name]` / `[experience_two_cert_name]` | `experience_one_cert_name` / `experience_two_cert_name` | `onlyInputEnglishCharacterValidation`; `string max 30` — **rule bug** (line 446): the `tooLong`-message rule array is `[['experience_one_cert_name', 'experience_one_cert_name'], ...]`, i.e. `experience_one_cert_name` listed twice by copy/paste instead of `experience_two_cert_name` — `experience_two_cert_name` gets the generic Yii `tooLong` message, not the custom Bangla one. | — |
| — (no UI anywhere, confirmed via grep) | `experience_three_institute/_subject/_year/_cert_name`, `experience_four_institute/_subject/_year/_cert_name` | covered by the same `max 150`/`max 50` string rules (lines 418, 420) as siblings | **Dead columns**: `rules()` validates them (they're in the shared string-length arrays) but no view anywhere renders `Sailors[experience_three_*]` or `Sailors[experience_four_*]`. |
| **Freedom fighter / naval-child / anser-vdp / khudro-jati-gosti block** | | | |
| `[is_freedom_fighter]` (dropDownList, active) | `is_freedom_fighter` | `personalInformationValidate`; `integer` | — |
| — (dropdown commented out) | `freedom_fighter_relation` | `freedomFighterRelationValidation` custom validator — **per `docs/00-inventories/model_inventory.md` cross-cutting finding #11, this validator's body is commented out and it always returns `true`** — a permanent no-op; `integer` | Effectively unvalidated even if it were reachable from the UI, which it isn't (field commented out of the view). |
| `[is_child_of_naval_officer]` (**hardcoded `hiddenInput` value=`2`/No**) | `is_child_of_naval_officer` | `personalInformationValidate`; `integer`; gates `isNavalChildValidation` on `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank` | **Confirmed bug (found by reading `SailorCandidateController.php`):** the controller pre-computes `is_child_of_naval_officer = YES` from the candidate's `CanEligibilityCheckInfo` record, but only on the initial GET render (that block sits after the `$sailor->load($_POST)` branch). Because the view unconditionally posts a hardcoded `Sailors[is_child_of_naval_officer]=2`, **every single form submission silently overwrites the eligibility-computed naval-child status back to "No,"** regardless of the candidate's real status. The entire `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank` detail fieldset is commented out of the view, so there is no way for a candidate to correct this. |
| `[is_anser_vdp]` (**hardcoded `hiddenInput` value=`2`/No**) | `is_anser_vdp` | `personalInformationValidate`; `integer`; gates `isAnserVdpValidation` on `anser_vdp_rank`/`anser_vdp_office_no` | Same pattern as above — detail fieldset commented out, value always posts as No. |
| `[is_khudro_jati_gosti]` (**hardcoded `hiddenInput` value=`2`/No**) | `is_khudro_jati_gosti` | `personalInformationValidate`; `integer` | Same pattern — always posts No, no UI path to set Yes. |
| `[photo]` (fileInput) | `photo` | `required` on `PERSONAL_INFO`; `image` validator: extensions `png, jpg`, `maxSize` 500 KB, **exactly** 300×300 px (`minWidth`/`maxWidth`/`minHeight`/`maxHeight` all `= 300`) | Two near-identical rule blocks exist (lines 454–469 and 470–484) — one per scenario (`PERSONAL_INFO` vs `PERSONAL_INFO_WITH_IMAGE`), otherwise byte-identical. |

**Not on this form / server-computed or system-set only:** `eligibility_info_id`, `app_unique_id` (both `required` unconditionally, set at the eligibility-check step before a `Sailors` row exists), `candidate_type`, `candidate_designation`, `center_id`, `batch_id`, `batch_config_id`, `exam_group`, `exam_date_id`, `exam_date`, `serial_no` (unique — the roll number), `serial_generate_date`, `eligible_district`, `qr_photo`, `application_status`, `payment_status`, `phase`, `is_manula_paid`, `have_reference`, `is_online_manual`, `is_departmental_candidate`, `team`, `cancel_application_view` (default 2), `request_for_cancel` (default 0), `permanent_phone_de` (pre-encrypted duplicate of `permanent_phone`, confirmed used for encrypted-substring search in `SailorsSearch`), all payment fields (`payment_type`/`agree_payment_terms`/`refund_phone`/etc. — see §1c), `is_image_exist_check`, `is_qr_exist_check`, `reference_add_on`, `last_reference_added`, `reference_details`, `relationship`, `referred_by`.

---

## 1b. Sailors — Academic Info step

- **UI:** `frontend/views/sailor-candidate/academic_info.php`
- **Scenario:** `ACADEMIC_INFO_JSC` if `candidate_designation == TOPASS` (primary-key constant), else `ACADEMIC_INFO_JSC_SSC` — **same view file serves both**, only the active custom validator differs.

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `[jsc_institute_name]` | `jsc_institute_name` | `jscAcademicInfoValidate` (scenario `ACADEMIC_INFO_JSC`) or part of `jscSscAcademicInfoValidate` (scenario `ACADEMIC_INFO_JSC_SSC`) — plus the always-on character/length rules from §1a | — |
| `[jsc_reg_no]` | `jsc_reg_no` | same as above | — |
| `[jsc_passing_year]` | `jsc_passing_year` | same as above | — |
| `[jsc_gpa]` | `jsc_gpa` | same as above | — |
| — (hiddenInput, error-message anchor only) | *(none — public property, not a column)* | listed `safe` (line 414) | `academic_info_already_used_in_jsc` is a declared public property on `Sailors`, **not** a DB column — mirrors the officer app's `noc_force`/`noc_result` pattern (validated field with no backing column, used purely to surface a duplicate-application error next to the JSC block). |
| `[ssc_edu_board]` (dropDownList) | `ssc_edu_board` | part of `jscSscAcademicInfoValidate` (scenario `ACADEMIC_INFO_JSC_SSC` only) + always-on rules | Not rendered/required in the `ACADEMIC_INFO_JSC`-only scenario. |
| `[ssc_roll_no]` | `ssc_roll_no` | same | — |
| `[ssc_reg_no]` | `ssc_reg_no` | same | — |
| `[ssc_passing_year]` | `ssc_passing_year` | same | — |
| — (not rendered — filled server-side) | `ssc_institute`, `ssc_group`, `ssc_gpa`, `ssc_additional_subject` | still `rules()`-validated (character/length rules always on) | Populated from the TeleTalk result-verification API by the controller after `load()`, overwriting whatever was posted — see Controller note below. |
| — (error anchor) | *(none — public property)* | `safe` | `academic_info_already_used_in_ssc`, same pattern as the JSC one above. |
| `[hsc_dip_board]` (dropDownList) | `hsc_dip_board` | **no dedicated scenario-gated required validator found in `rules()`** — only the always-on character/length rules apply | Unlike JSC/SSC, there is no `hscAcademicInfoValidate`-style custom validator visible in the `rules()` block read for this document; HSC/diploma appears to be either optional at this step or enforced purely client-side / re-validated on the Personal Info step (§1a) where the same columns reappear. |
| `[hsc_dip_roll_no]` | `hsc_dip_roll_no` | same (always-on rules only) | — |
| `[hsc_dip_reg_no]` | `hsc_dip_reg_no` | same | — |
| `[hsc_dip_passing_year]` | `hsc_dip_passing_year` | same | — |
| — (not rendered — filled server-side) | `hsc_dip_institute`, `hsc_dip_group`, `hsc_dip_gpa`, `hsc_dip_additional_subject` | always-on rules | Server-filled from TeleTalk, same pattern as SSC. |

**Controller note (`SailorCandidateController.php`):** after `$model->load($_POST)`, when TeleTalk verification is in effect the controller **overwrites** the posted `ssc_institute`, `ssc_group`, `ssc_reg_no`, `ssc_passing_year`, `ssc_gpa`, `ssc_roll_no`, `name`, `father_name`, `mother_name`, `gender`, `dob` (and the HSC equivalents) directly from the TeleTalk API response, regardless of what the view actually posted. This is the one systematic "view field name ≠ what actually gets saved" pattern found for `Sailors` — not a differently-named field, but a same-named field whose posted value is discarded and replaced.

---

## 1c. Sailors — Payment step

- **UI:** `frontend/views/sailor-candidate/payment.php`
- **Scenario:** `PAYMENT` (`'select_payment_type'`)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `[birth_registration_no]` | `birth_registration_no` | `required` on `PAYMENT`; `birthRegistrationNoValidation` custom validator; also `safe` (line 414, redundant with the required rule) | Controller pre-fills from `Yii::$app->user->identity->birth_registration_no`. |
| `[payment_type]` (radioList) | `payment_type` | `required` on `PAYMENT`; `paymentTypeValidate` custom; `string` (line 416) | — |
| `[agree_payment_terms]` (radioList, single option) | *(none — public property, not a column)* | `required` on `PAYMENT`; also `safe` | Not a DB column — a UI-only consent checkbox modeled as a public property, same class of field as `exam_center_name`. |
| **Separate modal form** → posts to `sailor-candidate/refund-phone` (only rendered if the batch allows refund) | | | |
| `[refund_phone]` | `refund_phone` | `safe` (line 411) | **Bug found by reading `actionRefundPhone()`:** this action does **not** call `$model->load()`/`validate()` at all. It reads the raw `Yii::$app->request->post()['Sailors']` array directly, applies a hand-rolled empty/regex check, encrypts the value, and calls `$sailorModel->save(false)` — **`save(false)` skips `rules()` entirely**, so the `safe` declaration for `refund_phone` is dead documentation as far as this endpoint is concerned; any format validation happening is only the inline regex in the controller, not the model. |
| `[id]` (hiddenInput) | `id` | n/a (primary key, used only to look up the row) | — |

---

## 1d. Sailors — Application Preview step

- **UI:** `frontend/views/sailor-candidate/application_preview.php`, `application_verify_preview.php`

Fully read-only — no form fields on either view (confirmed via grep). Two `Html::beginForm()` blocks with **no input fields**, one posting to the personal-info edit action, one to `actionCompleteApplication`.

**Bug/finding:** `actionCompleteApplication()` does **not** call `$model->load()` at all — it derives `serial_no` (roll number), `exam_date`, `exam_group`, `team`, and `batch_config_id` purely from server-side batch-configuration lookups and calls `save(false)`, bypassing `rules()` entirely for the final submit step. This is consistent with the pattern in §1c: the two most consequential write paths in the whole `Sailors` wizard (`refund-phone`, `complete-application`) both skip model validation by design.

**`candidate/*` views** (`frontend/views/sailor-candidate/candidate/*.php` — `my_application`, PDF/print views) are read-only display, no form fields.

---

## 2. DeSailors — the "Direct Entry" twin

- **DB table:** `de_sailors` (`{{%de_sailors}}`) — no migration, live DB only.
- **Model:** `common/models/DeSailors.php`. `rules()` quoted verbatim in `docs/00-inventories/model_inventory.md` lines 112–161.
- **Scenario constants:** `PAYMENT = 'select_payment_type'`, `ACADEMIC_DE_SAILOR_ARTIFICER = 'artificer'`, `ACADEMIC_DE_SAILOR_DOCKYARD = 'dockyard'`, `ACADEMIC_INFO_JSC = 'academic_info_jsc'`, `ACADEMIC_INFO_JSC_SSC = 'academic_info_jsc_ssc'`, `PERSONAL_INFO = 'personal_information'`, `PERSONAL_INFO_WITH_IMAGE = 'personal_information_with_image'`.
- **Public non-column properties:** `$skipTeleTalkValidation = true` (default), `$agree_payment_terms`, `$exam_center_name`, `$academic_info_already_used_in_ssc`, `$academic_info_already_used_in_jsc`.
- **UI:** `frontend/views/de-sailor/{payment,academic_info,personal_info,application_preview,application_verify_preview}.php`
- **Controller:** `frontend/controllers/DeSailorController.php`

**Wizard order (as read from the controller/routing) is `Payment → Academic Info → Personal Info`** — notably **different** from `Sailors`'s `Personal Info → Academic Info → Payment` order documented in §1. Both models share the same scenario-constant names and near-identical field families, but the DE track collects payment intent first.

### 2a. Payment step (`payment.php`, scenario `PAYMENT`)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `[birth_registration_no]` | `birth_registration_no` | `required` on `PAYMENT`; `birthRegistrationNoValidation` | Controller pre-fills from `Yii::$app->user->identity->birth_registration_no`, same as `Sailors`. |
| `[payment_type]` (radioList) | `payment_type` | `required` on `PAYMENT`; `string` | — |
| `[agree_payment_terms]` (radioList, single option) | *(none — public property)* | `required` on `PAYMENT` | Same UI-only pattern as `Sailors`. |

### 2b. Academic Info step (`academic_info.php` — **one view serves both Artificer and Dockyard tracks**; only the `$courses_list` data source and label text differ, no field-name divergence)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `[jsc_institute_name]` | `jsc_institute_name` | `sailorArtificerValidation` custom validator — **used for both `ACADEMIC_DE_SAILOR_ARTIFICER` and `ACADEMIC_DE_SAILOR_DOCKYARD` scenarios** (same validator name/field list for both, per `rules()` lines 119–120 of `DeSailors.php`) | The Dockyard scenario reusing an "Artificer"-named validator (rather than a dedicated `sailorDockyardValidation`) suggests the two trade tracks were never given distinct academic-eligibility logic — worth flagging for anyone tightening Dockyard-specific rules later. |
| `[jsc_reg_no]` | `jsc_reg_no` | same | — |
| `[jsc_passing_year]` | `jsc_passing_year` | same | — |
| `[jsc_gpa]` | `jsc_gpa` | same | — |
| — (error anchor) | *(none — public property)* | `safe` | `academic_info_already_used_in_jsc`, same pattern as `Sailors` §1b. |
| `[ssc_edu_board]` (dropDownList) | `ssc_edu_board` | `sailorArtificerValidation` | — |
| `[ssc_roll_no]` | `ssc_roll_no` | same | — |
| `[ssc_reg_no]` | `ssc_reg_no` | same | — |
| `[ssc_passing_year]` | `ssc_passing_year` | same | — |
| `[ssc_gpa]` / `[ssc_group]` (**only rendered when `$skipTeleTalkValidation === true`**) | `ssc_gpa` / `ssc_group` | always-on `onlyDecimalNumberInputValidation`/`onlyInputEnglishCharacterValidation` + length rules | `$skipTeleTalkValidation` is a public model property (default `true`) that toggles manual entry vs TeleTalk-API auto-fill — orthogonal to the Artificer/Dockyard scenario split. |
| — (error anchor) | *(none — public property)* | `safe` | `academic_info_already_used_in_ssc`. |
| `[hsc_dip_board]` (dropDownList) | `hsc_dip_board` | always-on rules | — |
| `[hsc_dip_roll_no]` | `hsc_dip_roll_no` | always-on rules | — |
| `[hsc_dip_reg_no]` | `hsc_dip_reg_no` | always-on rules | — |
| `[hsc_dip_passing_year]` | `hsc_dip_passing_year` | always-on rules | — |
| `[hsc_dip_gpa]` / `[hsc_dip_group]` (only when `skipTeleTalkValidation`) | same columns | always-on rules | — |
| `[diploma_trade_institute]` | `diploma_trade_institute` | `sailorArtificerValidation`; `string max 250` | DE-only field, absent from `Sailors`. |
| `[diploma_trade_course]` (dropDownList, `$courses_list`) | `diploma_trade_course` | `sailorArtificerValidation` | DE-only. |
| `[diploma_trade_registration_roll]` | `diploma_trade_registration_roll` | `sailorArtificerValidation`; `string max 50` | DE-only. |
| `[diploma_trade_gpa]` | `diploma_trade_gpa` | `sailorArtificerValidation`; `string max 50` | DE-only. |

**Controller note:** identical overwrite-after-`load()` pattern as `Sailors` §1b — when TeleTalk verification applies, the controller replaces posted `ssc_*`/`hsc_*`/`name`/`father_name`/`mother_name`/`gender`/`dob` with API-sourced values.

### 2c. Personal Info step (`personal_info.php`, scenarios `PERSONAL_INFO`/`PERSONAL_INFO_WITH_IMAGE`, multipart form)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `[district]` / `[gender]` (dropDownList) | `district` / `gender` | `integer`; `genderValidation` | — |
| `[exam_center_name]` (readonly) | *(none — public property)* | `safe` | — |
| `[name]` / `[name_bangla]` / `[permanent_phone]` / `[father_name]` / `[father_name_bangla]` / `[father_phone]` / `[father_nid]` / `[father_occupation]` / `[mother_name]` / `[mother_name_bangla]` / `[mother_phone]` / `[mother_occupation]` | same-named columns | `personalInformationValidate` (DeSailors' field list additionally includes `ssc_institute`, `diploma_trade_institute`, `diploma_trade_course`, `diploma_trade_registration_roll`, `diploma_trade_gpa` — a real divergence from `Sailors::personalInformationValidate`, which does **not** cover those); character-class + length rules per §2b/model_inventory | **Length-cap divergence from `Sailors`:** `DeSailors` does **not** carry the granular per-field `tooLong`-message overrides `Sailors` has (e.g. `father_occupation` capped at 30, `mother_occupation` at 15, villages at 25 — see §1a). Instead most of these fields fall under `DeSailors`'s blanket `string max 150` bucket (model_inventory.md line 151). Two structurally-twin models enforce materially different length limits on the same conceptual fields. |
| `[current_district]` / `[current_thana]` (AJAX-populated) | same | `personalInformationValidate`; character/length rules | — |
| `[current_union]` (**plain text, NOT a dropdown** — unlike `current_thana`) | `current_union` | `onlyInputEnglishCharacterValidation`; `max 150` | — |
| `[current_post_office]` / `[current_village]` / `[current_word_no]` / `[current_post_code]` | same-named columns | as above | — |
| — (commented out of view, same dead-field pattern as `Sailors` §1a) | `current_phone` | `phoneNoValidation` remains active in `rules()`; `max 150` | Confirmed by reading `personal_info.php` — the field is commented out despite being a validated column, mirroring the exact `Sailors` bug. |
| Permanent-address block (`[permanent_district]`, `[permanent_thana]`, `[permanent_union]`, `[permanent_post_office]`, `[permanent_village]`, `[permanent_word_no]`, `[permanent_post_code]`, `[permanent_phone]`) | mirrors current-address columns | same pattern | — |
| `[guardian_name]` / `[guardian_phone]` / `[guardian_relation]` / `[guardian_occupation]` / `[guardian_address]` (textarea) | same-named columns | `onlyInputEnglishCharacterValidation` (except phone); `phoneNoValidation` for guardian_phone; `max 150`/`max 250` per §2b bucket rules | — |
| `[dob]` | `dob` | `personalInformationValidate`; `ageValidation` on `PERSONAL_INFO`/`PERSONAL_INFO_WITH_IMAGE`; `safe` | `ageValidation` uses `dept_can_max_age` instead of `max_age` when `is_departmental_candidate` is set — DE-specific "already serving" ceiling, per model_inventory §"Sailors vs. DeSailors". |
| `[religion]` / `[marital_status]` (dropDownList) | same-named columns | `personalInformationValidate`; `maritalStatusValidation`; `integer` | — |
| `[nationality]` | `nationality` | `personalInformationValidate`; `max 150` | — |
| Academic re-display block (JSC/SSC/HSC-diploma/diploma-trade, same names as §2b, conditional hiddenInput/textInput uppercase-once-set pattern) | same columns as §2b | same rules as §2b | — |
| `[experience_one_institute/_subject/_year/_cert_name]`, `[experience_two_institute/_subject/_year/_cert_name]` (**only "one"/"two" rendered anywhere, confirmed via grep**) | same-named columns | `onlyInputEnglishCharacterValidation`/`onlyNumberInputValidation`; length rules per model_inventory line 150–151 | `experience_three_*`/`experience_four_*` exist in `rules()`'s length-cap arrays but are **dead** — no view renders `DeSailors[experience_three_*]` or `[experience_four_*]`, same as `Sailors` §1a. |
| `[is_freedom_fighter]` (dropDownList, active) | `is_freedom_fighter` | `personalInformationValidate`; `integer` | — |
| — (commented out entirely) | `freedom_fighter_relation` | n/a in this view | Unlike `Sailors`, there is no hidden/dead input for this at all in `DeSailors`'s personal_info view — fully absent. |
| `[is_child_of_naval_officer]` (**hardcoded `hiddenInput` value=`2`/No**) | `is_child_of_naval_officer` | `personalInformationValidate`; `integer`; gates `isNavalChildValidation` | **Same bug class as `Sailors` §1a**, with one twist: the controller force-sets `is_child_of_naval_officer = YES` (plus `naval_office_no`/`naval_rank` from `CanEligibilityCheckInfo`) **only** when `candidate_type == POSSO_KOTA` (a specific departmental-candidate designation), and does so server-side bypassing the view entirely for that branch. For every other candidate type, the same "always posts hardcoded 2" overwrite bug from `Sailors` applies — the `naval_father_name`/`naval_uniform_civil`/`naval_office_no`/`naval_rank` detail block is fully commented out of the view. |
| `[is_anser_vdp]` (**hardcoded `hiddenInput` value=`2`**) | `is_anser_vdp` | `integer` | Detail block commented out — same dead pattern as `Sailors`. |
| `[is_khudro_jati_gosti]` (**hardcoded `hiddenInput` value=`2`**) | `is_khudro_jati_gosti` | `integer` | Same. |
| `[photo]` (fileInput) | `photo` | `required` on `PERSONAL_INFO`; `image` validator (png/jpg, ≤500 KB, exactly 300×300 px) | Identical constraint to `Sailors`. |

### 2d. Application Preview / read-only views

`application_preview.php`, `application_verify_preview.php`, and `candidate/{my_application,application_verification_pdf,application_form_pdf}.php` are read-only — no form fields (confirmed via grep). `candidate/application_form_download.php` is the one exception, but it's driven by a **different, unrelated model** — `common\models\DownloadDocuments` (a plain form model, not `DeSailors`) — see its own entry under "form models" in `docs/00-inventories/model_inventory.md` §7.

**No controller-level field-name remapping found for `DeSailors`** beyond the TeleTalk-overwrite pattern (§2b) — every view maps 1:1 via standard `load()`.

---

## 3. User — three frontend form models + one backend admin form, all writing/reading the same `user` table

- **DB table:** `user` (`{{%user}}`) — **the one table with real migration history** in this repo: `console/migrations/m130524_201442_init.php` creates the base 9 columns (`id, username, auth_key, password_hash, password_reset_token, email, status, created_at, updated_at`); `m190124_110200_add_verification_token_column_to_user_table.php` adds `verification_token`.
- **Model:** `common/models/User.php` (implements `\yii\web\IdentityInterface`). `rules()` at model_inventory.md lines 640–659 (quoted verbatim there).
- **Schema drift (per model_inventory.md §23):** 11 of ~20 columns the app actually reads/writes — `user_group, user_type, phone_no, dob, last_login_ip, last_logout, login_zone, os, created_dt, updated_dt, birth_registration_no` — are **not created by either migration file**, meaning the live DB schema was hand-altered outside Yii's migration system.

### 3a. Frontend candidate signup — `frontend/views/site/signup.php`, model `frontend\models\SignupForm` (NOT `User` directly)

| View Field | `SignupForm` property | `User` column ultimately written | Validation Rule | Notes |
|---|---|---|---|---|
| `SignupForm[username]` | `username` | `username` | `required`; `unique` against `User`; custom `usernameValidation()` (6–15 chars, ≥1 letter, ≥1 digit, no whitespace) | — |
| `SignupForm[email]` | `email` | `email` | `required`, email format | `User::rules()` has `email` `required` but its `unique` rule is **commented out** — duplicate emails are allowed at the `User` level; `SignupForm` doesn't add its own uniqueness check either. |
| `SignupForm[password]` | `password` | `password_hash` (hashed) | `required` | — |
| — (declared on `SignupForm`, no `$form->field()` call anywhere in `signup.php`) | `birth_registration_no`, `confirm_password`, `phone_no`, `dob`, `captcha` | `birth_registration_no`/`phone_no`/`dob` on `User` | All `required` (or compare-validated for `confirm_password`) in `SignupForm::rules()` | **View appears stale relative to the model** — five validated `SignupForm` fields (including the `required` `birth_registration_no`, `dob`, and `captcha`) have no corresponding input in the only signup view found. Either signup is currently broken for real users, or a second/newer signup view exists that this pass didn't locate — worth a follow-up grep before relying on this page. |

`SignupForm::signup()` (per model_inventory.md §Part 3) constructs a `User` directly: encrypts `phone_no` via `common\static\DataEncryption::dataEncrypt()`, sets `user_group='register'`, `user_type='candidate'`, and — notably — **`status = STATUS_ACTIVE` immediately**, not pending-verification, saved with `save(false)` (bypassing `User::rules()` entirely for this write path, the same "final write skips validation" pattern seen in `Sailors`/`DeSailors` §1c/§1d). The verification-email call inside `signup()` is present in source but commented out, so the email-verification machinery (`VerifyEmailForm`, `ResendVerificationEmailForm`) is effectively dormant.

### 3b. Frontend login — `frontend/views/site/login.php`, model `common\models\LoginForm`

| View Field | Property | Validation Rule | Notes |
|---|---|---|---|
| `LoginForm[username]` | `username` | `required`; `trim` | — |
| `LoginForm[password]` | `password` | `required`; `validatePassword` custom (also checks `user->user_type != $this->user_type`) | — |
| `LoginForm[rememberMe]` | `rememberMe` | `boolean` | — |
| — (declared, not rendered) | `captcha`, `user_type` | `captcha` `required` | Not present in `login.php`'s field list. |

`LoginForm::getLoginAddress($ip)` calls a third-party IP-geolocation API (`http://ipinfo.io/{ip}`) over plain HTTP on every login, no auth/timeout — flagged in model_inventory.md as a mild hardening concern, restated here since it fires from this exact form's submit path.

### 3c. Password reset / verification forms

| View | Model | Field | Notes |
|---|---|---|---|
| `requestPasswordResetToken.php` | `frontend\models\PasswordResetRequestForm` | `PasswordResetRequestForm[email]` | `exist` rule against `User` filtered to `STATUS_ACTIVE`. |
| `resetPassword.php` | `frontend\models\ResetPasswordForm` | `ResetPasswordForm[password]` | Constructor resolves `User::findByPasswordResetToken($token)` or throws. |
| `resendVerificationEmail.php` | `frontend\models\ResendVerificationEmailForm` (per its own `rules()`) | `ResetPasswordForm[email]` | **View/docblock mismatch found by the research pass**: the view's `@var` docblock declares `\frontend\models\ResetPasswordForm $model`, and the rendered field name literally reads `ResetPasswordForm[email]` — but the model that actually backs this page's controller action is `ResendVerificationEmailForm`. Either a stale copy-paste from `resetPassword.php`, or (less likely) the field silently binds to the wrong model's scope at runtime; worth a direct controller check before trusting this page's validation. |
| No `verify-email.php` view exists | `frontend\models\VerifyEmailForm` | n/a | Per model_inventory.md, `VerifyEmailForm` is the only form model with **zero** `rules()` — validation is implicit (constructor throws if the token doesn't resolve). Consistent with signup's verification flow being dormant (§3a). |

### 3d. Backend admin — `backend/views/user/_form.php` (single file for create + update)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `User[user_group]` (dropDownList) | `user_group` | `string` | Options hardcoded in the view: `super_admin`, `admin`, `register`. **`user_group`/`user_type` are NOT in either migration** (schema drift, see above). |
| `User[user_type]` (dropDownList) | `user_type` | `string` | Options hardcoded: `admin`, `candidate`. |
| `User[username]` | `username` | `required`; `unique`; `string max 255` | — |
| `User[email]` | `email` | `required`; `string max 255` | `unique` commented out (see §3a). |
| `User[phone_no]` | `phone_no` | `safe` | Not in either migration. |
| `User[password_hash]` (**plain `Html::activeTextInput`, not a password-type input**) | `password_hash` | `required`; `string max 255` | **Security-relevant finding**: the admin form renders the bcrypt hash as an editable plain-text field rather than accepting a new plaintext password and hashing it server-side (or masking the field) — an admin can view/copy/overwrite the raw hash directly through the UI. |
| `User[status]` (dropDownList via `StaticMethod::statusDropDown()`) | `status` | `default STATUS_INACTIVE`; `in [ACTIVE, INACTIVE, DELETED]` | — |

---

## 4. SailorBatchs — admin batch management

- **DB table:** `sailor_batchs` (`{{%sailor_batchs}}`) — no migration.
- **Model:** `common/models/SailorBatchs.php`, `rules()` at model_inventory.md lines 363–377.
- **UI:** `backend/views/sailor-batchs/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `SailorBatchs[candidate_type]` (dropDownList, `StaticMethod::candidateType()`) | `candidate_type` | `required`; `integer` | — |
| `SailorBatchs[name_en]` / `[name_bn]` | `name_en` / `name_bn` | `name_en` `required`; both `string max 255` | — |
| `SailorBatchs[description]` (textarea) | `description` | `string max 255` | Not in `required` list. |
| `SailorBatchs[circular_date]` (jui DatePicker) | `circular_date` | `required`; `safe` | — |
| `SailorBatchs[circular_start_date]` / `[circular_close_date]` (flatpickr `.circular_date_time`) | `circular_start_date` / `circular_close_date` | both `required`; `circular_close_date` also `safe` | — |
| — (**commented out — not currently rendered**) | `circular_media` | n/a | Has an `attributeLabels()` entry per model_inventory but no matching `rules()` entry and no current form field — likely a leftover/incomplete media-upload feature. |
| — (**commented out**) | `media_for_api` | n/a | Same — labeled but unrendered and unvalidated. |
| `SailorBatchs[roll_from]` (inline radioList, `StaticMethod::getRollFrom()`) | `roll_from` | `string` | — |
| `SailorBatchs[start_roll]` | `start_roll` | `required`; `number` | — |
| `SailorBatchs[next_start_roll]` / `[next_start_roll_after]` | `next_start_roll` / `next_start_roll_after` | `number` | Not in `required` list. |
| `SailorBatchs[payment_mode]` (inline radioList) | `payment_mode` | `integer` | — |
| `SailorBatchs[payment_amount]` (dropDownList) | `payment_amount` | `required`; `number`; `in => StaticMethod::paymentAmount()` allowed-value list | — |
| `SailorBatchs[allow_refund]` (inline radioList yes/no) | `allow_refund` | `required` | Gates the `Sailors[refund_phone]` modal in §1c. |
| `SailorBatchs[is_active_batch]` (inline radioList) | `is_active_batch` | `integer` | — |
| `SailorBatchs[allow_application_after_close]` (inline radioList) | `allow_application_after_close` | `integer` | — |
| `SailorBatchs[is_batch_live_mode]` (inline radioList) | `is_batch_live_mode` | `integer` | — |
| `SailorBatchs[secrate_key]` | `secrate_key` | `required`; `string max 50` | Column and field name both carry the "secrate" (should be "secret") misspelling — real, source-confirmed, not a transcription error here. |
| `SailorBatchs[status]` (dropDownList) | `status` | `integer` | — |

---

## 5. SailorBatchConfiguration + SailorBatchConfigurationExamDate — admin per-batch/center/group quotas and exam-date scheduling

- **DB tables:** `sailor_batch_configuration`, `sailor_batch_configuration_exam_date` — no migrations.
- **Models:** `common/models/SailorBatchConfiguration.php` (`rules()` at model_inventory.md lines 316–324) and `common/models/SailorBatchConfigurationExamDate.php` (`rules()` at lines 342–350).
- **UI:** single file, `backend/views/sailor-batch-configuration/_form.php` — exam dates are rendered **inline, hand-rolled** (a PHP loop for existing rows + a jQuery-duplicated HTML template for "Add More Exam Dates"), **not** a dedicated widget/helper class.

### 5a. SailorBatchConfiguration fields

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `SailorBatchConfiguration[candidate_type]` (dropDownList) | `candidate_type` | `required`; `integer` | — |
| `SailorBatchConfiguration[batch_id]` (dropDownList, `SailorBatchs::getAllActiveBatch()`) | `batch_id` | `required`; `integer` | No DB-level FK constraint — plain integer column, joined app-side via `SailorBatchConfiguration::getBatch()` relation. |
| `SailorBatchConfiguration[center_id]` (dropDownList, `SailorCenters::getAllActiveCenter()`, id `sailorbatchconfiguration-center_id`) | `center_id` | `required`; `integer` | JS `change` handler AJAX-loads `district_slug` options from `ajax/get-all-assigned-district-by-center`. |
| `SailorBatchConfiguration[team]` (dropDownList) | `team` | `required` | — |
| `SailorBatchConfiguration[gender]` (inline checkboxList) | `gender` | `required` | Array posted, imploded to CSV via `implodeFieldList()` `saving`-event hook (model lines 91–94/68–74 per model_inventory.md). |
| `SailorBatchConfiguration[marital_status]` (inline checkboxList) | `marital_status` | not required — only `safe` (line 320, listed alongside `exam_group`) | Note: unlike `gender`, `marital_status` is **not** in the `required` array (line 317) despite both being CSV-imploded checkbox lists. |
| `SailorBatchConfiguration[candidate_designation]` (Select2 multiple, `CanDesignation::getAllActiveDesignation()`) | `candidate_designation` | `safe` | Imploded to CSV, later matched with a `REGEXP "(^|,)id(,|$)"` pattern by `batchIdAndCenterIdByApplyDistrictAndDepartment()`. |
| `SailorBatchConfiguration[district_slug]` (Select2 multiple, data from `SailorCentDistMapping::GetAllAssignedDistrictByCenter($model->center_id)`, AJAX-repopulated on `center_id` change) | `district_slug` | `safe` | CSV-of-slugs, same encoding style as `SailorCentDistMapping::district_slug` (§6). |
| `SailorBatchConfiguration[exam_group]` (inline radioList) | `exam_group` | `required`; also `safe` (redundant, line 320) | — |
| `SailorBatchConfiguration[roll_swap_in_group]` (inline radioList yes/no) | `roll_swap_in_group` | `integer` | — |
| `SailorBatchConfiguration[check_max_candidate]` (inline radioList yes/no) | `check_max_candidate` | `required`; `integer` | — |
| `SailorBatchConfiguration[group_start_roll]` / `[group_end_roll]` (text, `enableAjaxValidation`) | `group_start_roll` / `group_end_roll` | `required`; `string max 50`; `validateOnRollSwap` custom validator | — |
| `SailorBatchConfiguration[group_no_of_candidate]` (text) | `group_no_of_candidate` | `required`; `string max 50` | — |
| `SailorBatchConfiguration[du_uc_can_total]` / `[medical_can_total]` / `[pertol_store_can_total]` / `[cook_steward_can_total]` / `[modc_can_total]` / `[topass_can_total]` (text) | same-named columns | `integer`; `string max 50` | Bangladesh Navy sailor trade-branch quota totals — labels per model_inventory.md line 326: `du_uc_can_total` = "DEUC (Seaman / Communication / Technical)", `pertol_store_can_total` = "Petrolman / Writer / Store", `modc_can_total` = "MODC (N)". |
| `SailorBatchConfiguration[status]` (dropDownList) | `status` | not in `required`/explicit rules quoted, but `integer` (line 318) | — |
| — (no form field — model-level `$hidden`) | `roll_swap_in_group`* / `exam_group`* | n/a | model_inventory.md flags these as also present in `BatchConfiguration::$hidden` in the **officer** app's analogous model; for `SailorBatchConfiguration` specifically, both `roll_swap_in_group` and `exam_group` **are** rendered as radioLists here — no such hidden-field gap was found in the sailor app's admin form (stated for completeness, not to imply a gap that doesn't exist here). |

### 5b. SailorBatchConfigurationExamDate fields (inline repeater within the same view)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `SailorBatchConfigurationExamDate[exam_date][]` (text, id `sailorbatchconfigurationexamdate-exam_date-N`, flatpickr `.dp`) | `exam_date` | `safe` (default value null) | Array-name syntax — one row per configured exam date. |
| `SailorBatchConfigurationExamDate[max_candidate_this_date][]` (number input, `min=1`) | `max_candidate_this_date` | `number` (default value null) | Enforced also at runtime by `SailorBatchConfigurationExamDate::getNextAvailableExamDate()`, which assigns candidates round-robin under this cap and flips over-limit dates to `status=2` (per model_inventory.md line 354). |
| `SailorBatchConfigurationExamDate[id][]` (**plain `<input type="hidden">`, not built via `$form->field()`**) | `id` | n/a (primary key) | Empty string for JS-added new rows; carries the existing row's PK for edits/deletes. |
| — (JS-only, not a posted field) | n/a | n/a | "Add More Exam Dates" button appends hand-written HTML via jQuery, duplicating the exact `name="SailorBatchConfigurationExamDate[...][]"` attributes seen in the server-rendered rows — a hand-rolled repeater, not a Yii widget or JS component library. |
| — (AJAX DELETE, not a form field) | n/a | n/a | The row-delete icon fires an AJAX `DELETE` to `sailor-batch-configuration/delete-exam-date` rather than submitting the parent form. |
| — (dead code, never wired up) | n/a | n/a | Per model_inventory.md line 354: `SailorBatchConfigurationExamDate::validateExamDate()` exists (with a stray `echo 'ds';` debug statement) but is never referenced from `rules()` — a no-op method left in the model. |

---

## 6. SailorCenters + SailorCentDistMapping — exam centers and their assigned districts

- **DB tables:** `sailor_centers`, `sailor_cent_dist_mapping` — no migrations.
- **Models:** `common/models/SailorCenters.php` (`rules()` model_inventory.md lines 417–425); `common/models/SailorCentDistMapping.php` (`rules()` lines 392–401).

### 6a. SailorCenters — `backend/views/sailor-centers/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `SailorCenters[candidate_type]` (dropDownList) | `candidate_type` | `required`; `integer` | Doc comment: 1=Sailor, 2=DE Sailor. |
| `SailorCenters[name_en]` / `[name_bn]` | `name_en` / `name_bn` | `name_en` `required`; both `string max 255`; both `strip_tags`-filtered | — |
| `SailorCenters[status]` (dropDownList) | `status` | `integer` | — |
| — (no `id` field on the form, no direct district linkage) | n/a | n/a | The center↔district relationship lives entirely in the separate `SailorCentDistMapping` table/form (§6b) — `SailorCenters` itself has no `district_slug`-style column. |

### 6b. SailorCentDistMapping — `backend/views/sailor-cent-dist-mapping/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `SailorCentDistMapping[candidate_type]` (dropDownList) | `candidate_type` | `required`; `integer` | — |
| `SailorCentDistMapping[center_id]` (dropDownList, `SailorCenters::getAllActiveCenter()`) | `center_id` | `required`; `integer`; `exist` rule against `SailorCenters` (`targetAttribute => ['center_id' => 'id']`) | The only model in this document whose `rules()` uses a real `exist`-validator FK check rather than a plain `integer` rule. |
| `SailorCentDistMapping[district_slug]` (kartik Select2 multiple, `Districts::getAllActiveDistrict()` slug⇒name) | `district_slug` | `safe`; `string` (implicitly, no explicit length rule quoted) | Multi-select posts an array; model implodes to CSV on save (`beforeSave()`/`saving` hook per model_inventory.md line 406). **No AJAX cascade** down to `Upozilas`/`Unions` — this is purely District-level, one row mapping one center to a CSV list of district slugs, not a true FK junction table. |
| `SailorCentDistMapping[status]` (dropDownList) | `status` | `integer` | — |
| — (validated but not on this form) | `created_by` / `updated_by` | `exist` rules against `User` | Set by `beforeSave()`, not user-entered. |

---

## 7. Eligibility — admin eligibility-criteria configuration per candidate_type/designation

- **DB table:** `eligibility` (`{{%eligibility}}`) — no migration.
- **Model:** `common/models/Eligibility.php`, `rules()` at model_inventory.md lines 247–258.
- **UI:** `backend/views/eligibility/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `Eligibility[candidate_type]` (dropDownList; AJAX `change` → `/ajax/get-candesignation-by-cantype` repopulates the designation dropdown) | `candidate_type` | `required`; `integer` | — |
| `Eligibility[candidate_designation]` (dropDownList, `CanDesignation::getAllActiveDesignation()`, AJAX-repopulated) | `candidate_designation` | `required`; `integer`; combined `unique` with `candidate_type` (`targetAttribute => ['candidate_designation', 'candidate_type']`) | Only one eligibility row per type+designation pair. |
| `Eligibility[marital_status]` (inline checkboxList) | `marital_status` | `required` | Imploded to CSV in `beforeSave()`. |
| `Eligibility[gender]` (inline checkboxList) | `gender` | `required` | Imploded to CSV. |
| `Eligibility[min_age]` / `[max_age]` / `[dept_can_max_age]` (text, format hint like `17.00.00`) | same-named columns | `required`; `string max 50` | `dept_can_max_age` used instead of `max_age` for departmental candidates per `ageValidation()` in `Sailors`/`DeSailors` (§1a/§2c). |
| `Eligibility[jsc_result]` (inline radioList yes/no) | `jsc_result` | `required`; `integer` | — |
| `Eligibility[ssc_result]` / `[hsc_result]` / `[diploma_result]` (text) | same-named columns | `number`; `max 5` (all three, line 253); `string max 50` (line 256) | — |
| `Eligibility[ssc_ac_group]` / `[hsc_ac_group]` (inline checkboxList) | same-named columns | `safe` | Not `required` despite being a checkbox list, unlike `marital_status`/`gender`. |
| `Eligibility[height_male]` / `[height_female]` / `[weight_male]` / `[weight_female]` / `[chest_normal_male]` / `[chest_extended_male]` / `[chest_normal_female]` / `[chest_extended_female]` (text) | same-named columns | `number`; `string max 50` | — |
| `Eligibility[is_required_biology]` (inline radioList yes/no) | `is_required_biology` | `required`; `integer` | Feeds `CanEligibilityCheckInfo`'s biology-requirement gating (§8). |
| `Eligibility[is_allow_trade_course]` (inline radioList yes/no) | `is_allow_trade_course` | `integer` | — |
| `Eligibility[is_allow_diploma]` (inline radioList yes/no) | `is_allow_diploma` | `integer` | — |
| `Eligibility[is_required_trade_course_experience]` (inline radioList yes/no) | `is_required_trade_course_experience` | `required`; not explicitly typed beyond the general `integer` list check — see model_inventory.md line 251 for the `required` grouping | — |
| `Eligibility[trade_course_subject]` (Select2 multiple, `Subjects::getAllActiveSubjectBySubjectType(candidate_type: DE_SAILOR_DOCKYARD, subject_type: TRADE)`) | `trade_course_subject` | `safe` | Feeds `CanEligibilityCheckInfo::validateTradeCourseSubject()` (§8). |
| `Eligibility[hons_diploma_subject]` (Select2 multiple, `Subjects::getAllActiveSubjectBySubjectType(DE_SAILOR, DIPLOMA)`) | `hons_diploma_subject` | `safe` | — |
| `Eligibility[status]` (dropDownList) | `status` | `integer` | — |
| — (**commented out of the current form**) | `hons_diploma_subject` (old dropdown variant), `is_allow_hons_appeared`, `hons_result`, `is_allow_masters_appeared`, `masters_result`, `masters_subject` | `is_allow_hons_appeared`/`is_allow_masters_appeared`/`masters_subject` are `integer`/`string max 50` in `rules()`; `hons_result`/`masters_result` are `string max 50` | model_inventory.md's own `attributeLabels()` note flags `is_allow_hons_appeared`/`is_allow_masters_appeared` labels as literally reading `'1=>Yes, 2=>No'` (a Gii-boilerplate artifact never cleaned up) — consistent with these fields being half-abandoned: still validated, but commented out of the actual admin form. |
| — (no matching column) | `center_id[]` | n/a | Unlike the officer-legacy app's `EligibilitySetting`, `Eligibility` here has **no `center_id` column at all** — no dead/leftover checkbox for it either; center↔designation targeting for sailor batches happens entirely through `SailorBatchConfiguration` (§5), not `Eligibility`. |

---

## 8. CanEligibilityCheckInfo — frontend "check your eligibility" pre-screening tool

- **DB table:** `can_eligibility_check_info` (`{{%can_eligibility_check_info}}`) — no migration.
- **Model:** `common/models/CanEligibilityCheckInfo.php`, `rules()` quoted verbatim in model_inventory.md lines 46–74.
- **Scenarios:** `SCENARIO_PERSONAL_INFO = 'personal_info'`, `SCENARIO_ACADEMIC_INFO = 'academic_info'` (`'on' =>` targets, no `scenarios()` override).
- **Public non-column properties:** `$height_feet`, `$height_inch`.
- **UI:** `frontend/views/check-eligibility/{personal_info.php,academic_info.php,eligible_department.php}.php`

### 8a. Personal Info step (`personal_info.php`, `SCENARIO_PERSONAL_INFO`)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `CanEligibilityCheckInfo[candidate_type]` (**hiddenInput, hardcoded value=1**) | `candidate_type` | `required` on `SCENARIO_PERSONAL_INFO`; `integer` | The dropdown version of this field is commented out of the view, along with its AJAX handler (`/ajax/district-by-candidate-type`) — the tool currently only ever pre-screens for `candidate_type = 1`, with no UI path to select a different type despite the field being nominally selectable. |
| `CanEligibilityCheckInfo[dob]` (yii\jui\DatePicker, readonly text, `dd-MM-yyyy`) | `dob` | `required`; `safe` | — |
| `CanEligibilityCheckInfo[gender]` (dropDownList) | `gender` | `required`; `integer` | — |
| `CanEligibilityCheckInfo[nationality]` (dropDownList, single hardcoded option `bangladeshi`) | `nationality` | `string max 100` | Not in the `required` scenario list; effectively fixed to one value by the UI. |
| `CanEligibilityCheckInfo[marital_status]` (dropDownList) | `marital_status` | `required`; `integer` | — |
| `CanEligibilityCheckInfo[height_feet]` / `[height_inch]` (text, client-side JS converts to cm on keyup) | *(none — both are public properties, not DB columns)* | `required` (both, on `SCENARIO_PERSONAL_INFO`); `height_feet` `integer min 5 max 7`; `height_inch` `integer min 0 max 11` | Neither is a `can_eligibility_check_info` column — the model's own `@property`-equivalent doc comment/property declarations mark them as non-persisted helper inputs; the computed centimeter value presumably lands in a real `height` column, which isn't independently rendered as its own field here (server-computed from these two before save). |
| `CanEligibilityCheckInfo[chest_normal]` / `[chest_expanded]` (text) | `chest_normal` / `chest_expanded` | `integer min 25 max 38` | — |
| `CanEligibilityCheckInfo[eye_status]` (dropDownList) | `eye_status` | `integer` | Label: "Eye Power" (model_inventory.md line 76). |
| `CanEligibilityCheckInfo[district]` (kartik Select2 single-select, `Districts::getAllActiveDistrict()`) | `district` | `required` on `SCENARIO_PERSONAL_INFO`; `string max 150` | AJAX repopulation-by-candidate-type is effectively dead code since `candidate_type` is a hardcoded hidden field (see above) — the district list never actually changes based on it. |
| — (**commented out entirely**) | `p_o_no`, `rank` | `validatePoNoRank` + `validateEnglishInput` custom validators; `string max 200` | The "P.O. No / O No" and departmental-rank block is fully commented out of the current `personal_info.php` — validated in `rules()`, unreachable from this view. |

### 8b. Academic Info step (`academic_info.php`, `SCENARIO_ACADEMIC_INFO`)

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `CanEligibilityCheckInfo[jsc_result]` (inline radioList pass/fail) | `jsc_result` | `required` on `SCENARIO_ACADEMIC_INFO` | — |
| `CanEligibilityCheckInfo[ssc_equv_group]` (dropDownList, `enableAjaxValidation`, toggles biology/trade blocks) | `ssc_equv_group` | `validateSscEquvGroup` custom validator; `integer` | — |
| `CanEligibilityCheckInfo[ssc_equv_result]` (text) | `ssc_equv_result` | `number min 2.5 max 5`; `validateSscEquvResult` custom validator | — |
| `CanEligibilityCheckInfo[ssc_equv_is_trade_course_complete]` (inline radioList yes/no, toggles diploma/trade-experience blocks) | `ssc_equv_is_trade_course_complete` | `validateTradeCourseComplete` custom; `integer` | — |
| `CanEligibilityCheckInfo[trade_course_subject]` (**plain dropDownList here — NOT Select2**, single-select, unlike `Eligibility[trade_course_subject]`'s multi-select in §7) | `trade_course_subject` | `validateTradeCourseSubject` custom; `string max 50` | Interesting UX asymmetry: the admin configures *allowed* trade subjects as a multi-select list (§7), but the candidate-facing check-eligibility tool only lets the candidate pick one. |
| `CanEligibilityCheckInfo[have_trade_course_experience]` (inline radioList yes/no) | `have_trade_course_experience` | `validateHaveTradeCourseExperience` custom; also `safe` (line 66) | — |
| `CanEligibilityCheckInfo[ssc_equv_is_biology_include]` (inline radioList yes/no) | `ssc_equv_is_biology_include` | `validateBiologyIsInclude` custom; `integer` | Bangladesh-navy-specific: biology inclusion is required when SSC group is Science/Vocational — enforced entirely inside the custom validator, not visible in a declarative rule. |
| `CanEligibilityCheckInfo[hsc_equv_academic_type]` (inline radioList; AJAX `/ajax/hsc-diploma-ac-group` repopulates `hsc_equv_group` and swaps the result-field placeholder text between "HSC" and "Diploma") | `hsc_equv_academic_type` | `integer` | — |
| `CanEligibilityCheckInfo[hsc_equv_group]` (dropDownList, AJAX-repopulated) | `hsc_equv_group` | `string max 50` | — |
| `CanEligibilityCheckInfo[hsc_equv_result]` (text) | `hsc_equv_result` | `string max 50` | No explicit `number`/`min`/`max` numeric-range rule quoted for this one, unlike `ssc_equv_result` — worth confirming against the live model if tightening validation later. |

### 8c. `eligible_department.php` — results-only, no form fields

Displays the computed eligible-department result; not a form submission target.

---

## 9. Districts / Unions / Upozilas — Bangladesh administrative hierarchy, admin-managed

- **DB tables:** `districts`, `unions`, `upozilas` — no migrations.
- **Models:** `common/models/Districts.php` (`rules()` model_inventory.md lines 199–210), `Unions.php` (lines 588–597), `Upozilas.php` (lines 613–622).
- **Structural note (per model_inventory.md §"Note" after Upozilas):** District → Upazila → Union is the standard Bangladesh administrative hierarchy, but **none of the three models declare AR relations to each other** — every cross-level lookup goes through static helper methods keyed on raw `district_id`/`upozila_id`/slug columns, confirmed to extend to the admin forms below: **no AJAX cascading exists in any of the three `_form.php` files** — Upozila/Union dropdowns always show the full unfiltered list regardless of the selected parent.

### 9a. Districts — `backend/views/districts/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `Districts[division]` (dropDownList) | `division` | `required` | — |
| `Districts[name_en]` / `[name_bn]` | `name_en` / `name_bn` | `name_en` `required`; both `unique` (name_en only, per rule tuple)/`string max 255`; `strip_tags`-filtered | `SlugBehavior` auto-derives `slug` from `name_en` (5–20 chars, lowercase, `-` separator, `ensureUnique`) — `slug` itself is never a form field, it's behavior-generated. |
| `Districts[status]` (dropDownList) | `status` | `integer` | — |

### 9b. Unions — `backend/views/unions/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `Unions[district_id]` (dropDownList) | `district_id` | `required`; `integer` | No cascading — full district list always shown. |
| `Unions[upozila_id]` (dropDownList, `Upozilas::getUpazilaListAdmin()`) | `upozila_id` | `required`; `integer` | **No AJAX cascading to `district_id`** — the full, unfiltered upazila list is always shown regardless of the district selected, confirmed by reading the view. |
| `Unions[name]` / `[bn_name]` | `name` / `bn_name` | `name` `required`; both `string max 250` | — |
| `Unions[status]` (dropDownList) | `status` | `integer` | — |
| — (declared in `rules()`, no `beforeSave()` to populate them) | `created_at`/`updated_at` | `safe` | Per model_inventory.md line 601: `Unions` (unlike `Districts`/`SailorCenters`/etc.) has **no `beforeSave()`** — these audit-timestamp columns are declared safe-for-mass-assignment but never actually auto-populated by the model. Also note the `_at` suffix here vs. the `_dt` suffix used everywhere else in this app (`created_dt`/`updated_dt`) — a naming-convention inconsistency specific to `Unions`/`Upozilas`. |

### 9c. Upozilas — `backend/views/upozilas/_form.php`

| View Field | DB Column | Validation Rule | Notes |
|---|---|---|---|
| `Upozilas[district_id]` (dropDownList) | `district_id` | `required`; `integer` | **Label bug found by reading the view**: the dropdown's prompt text is pulled via `$model->getAttributeLabel('division')` instead of `getAttributeLabel('district_id')` — likely copy-pasted from the `Districts` form (§9a), where `division` is a real field; on `Upozilas` this mislabels the district selector with a label string that doesn't belong to this model at all. |
| `Upozilas[name]` / `[bn_name]` | `name` / `bn_name` | `name` `required`; both `string max 250` | — |
| `Upozilas[status]` (dropDownList) | `status` | `integer` | — |
| — (no `beforeSave()`, same as `Unions`) | `created_at`/`updated_at` | `safe` | Same audit-timestamp gap as §9b. |

---

## Cross-cutting findings (surfaced by cross-referencing `rules()` against the actual view/controller code — new material beyond what `model_inventory.md` already documents)

1. **Silent eligibility-status downgrade on every `Sailors`/`DeSailors` personal-info submit.** Both wizards hardcode `Sailors[is_child_of_naval_officer]`, `[is_anser_vdp]`, `[is_khudro_jati_gosti]` (and `DeSailors`'s equivalents) as `hiddenInput` fields fixed at value `2` ("No"). The controllers separately pre-compute the correct `is_child_of_naval_officer = YES` from `CanEligibilityCheckInfo` on initial page load, but because the hidden field always posts `2`, **every form save overwrites that computed status back to "No,"** and the detail fieldsets that would let a candidate self-report these statuses are commented out of both views. `DeSailors` has one carve-out (`candidate_type == POSSO_KOTA` forces it server-side, bypassing the view), but every other candidate is affected.
2. **Two consequential write paths skip model validation entirely via `save(false)`:** `Sailors::actionRefundPhone()` (reads raw POST, hand-rolled regex check only, no `load()`/`validate()`) and `Sailors::actionCompleteApplication()` (no `load()` at all, server-derived roll/exam-date fields only). `SignupForm::signup()` follows the identical pattern for `User` creation. In all three cases, the corresponding `rules()` entries (`refund_phone` `safe`, the full `Sailors`/`User` ruleset) are effectively decorative for that specific write path.
3. **`experience_three_*`/`experience_four_*` are dead columns** on both `Sailors` and `DeSailors` — present in every relevant `rules()` length-cap array, never rendered by any view (confirmed via grep across both wizards).
4. **`academic_info_already_used_in_jsc`/`_ssc` are non-column public properties**, not DB fields, on both `Sailors` and `DeSailors` — used purely as hidden error-message anchors next to the JSC/SSC academic blocks, structurally identical to the officer-legacy app's `noc_force`/`noc_result` pattern.
5. **`current_phone` is a dead field on both `Sailors` and `DeSailors` personal-info forms** — commented out of both views, yet still covered by an active `phoneNoValidation` rule and length constraint in both models' `rules()`.
6. **Two real rule bugs found by reading `Sailors.php` directly:** the `experience_one_cert_name`/`experience_two_cert_name` custom `tooLong` message rule (line 446) lists `experience_one_cert_name` twice instead of covering `experience_two_cert_name`; the `permanent_post_code` `tooLong` message (line 432) sources its text from `attributeLabelBangla()['current_thana']` instead of a post-code-specific label.
7. **`freedom_fighter_relation`'s custom validator (`Sailors::freedomFighterRelationValidation()`) is a permanent no-op** (body commented out, always returns `true` per model_inventory.md finding #11) — and separately, its input is commented out of the `Sailors` personal-info view entirely, so the field is doubly dead: unreachable from the UI, and would pass validation even if it weren't.
8. **Backend admin `User[password_hash]` is a plain, unmasked text input** editable directly through `backend/views/user/_form.php` — an admin can read and overwrite the raw bcrypt hash through the standard edit form rather than the form accepting a new plaintext password.
9. **`frontend/views/site/signup.php` renders only 3 of `SignupForm`'s 8 validated properties** (`username`, `email`, `password` — missing `birth_registration_no`, `confirm_password`, `phone_no`, `dob`, `captcha`, several of which are `required`); `resendVerificationEmail.php`'s field name (`ResetPasswordForm[email]`) and docblock both reference the wrong form-model class name for that page. Both are worth a direct runtime check before relying on these pages.
10. **No AJAX cascading anywhere in the District → Upazila → Union admin forms** (`backend/views/{districts,unions,upozilas}/_form.php`) — consistent with model_inventory.md's finding that none of the three models declare AR relations to each other; `Upozilas[district_id]`'s dropdown additionally carries a label bug, pulling its prompt text from `Districts`'s `division` attribute label instead of its own.
11. **`SailorBatchConfigurationExamDate`'s repeater UI is hand-rolled**, not a Yii widget — PHP-rendered rows and a jQuery-duplicated "Add More" template share the same `name="SailorBatchConfigurationExamDate[field][]"` array-index convention, with row deletion going through a separate AJAX `DELETE` endpoint rather than the parent form.
12. **Structural divergence between the "twin" models `Sailors` and `DeSailors`:** `DeSailors::personalInformationValidate()`'s field list additionally covers `ssc_institute` and all four `diploma_trade_*` columns (`Sailors`'s does not, since `Sailors` has no trade-diploma concept); and where `Sailors` gives many address/name fields individual, tightly-capped `tooLong`-messaged length rules (father_occupation ≤30, mother_occupation ≤15, villages ≤25, etc.), `DeSailors` instead buckets most of the same conceptual fields under one blanket `string max 150` rule with no custom message — two structurally-parallel wizards enforcing materially different limits on equivalent fields.
13. **Every table in this document except `user` has zero migration coverage** (restated from the document-level note above, repeated here because it is the single fact that most constrains any modernization effort) — schema for `sailors`, `de_sailors`, `sailor_batchs`, `sailor_batch_configuration`, `sailor_batch_configuration_exam_date`, `sailor_centers`, `sailor_cent_dist_mapping`, `districts`, `unions`, `upozilas`, `eligibility`, `can_eligibility_check_info` must be reconstructed entirely from the `rules()` blocks quoted/cited in this document.
