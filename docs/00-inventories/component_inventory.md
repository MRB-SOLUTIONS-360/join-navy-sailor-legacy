# Component Inventory — `join-navy-sailor-legacy`

**Scope:** every reusable UI component/widget/partial in `common/`, `frontend/`, `backend/` — Yii2 `Widget` subclasses, `common/components/` service components, true `$this->render()`/`renderPartial()` partials, and de-facto repeated UI patterns (markup + JS-plugin init copy-pasted across views rather than extracted). Evidence is file:line or grep counts; every file named below was opened and read directly.

Architecture note vs. the officer-portal Laravel/Blade app: this is a stock **Yii2 2.0 advanced template** (two apps — `frontend/` public candidate portal, `backend/` admin panel — sharing one `common/` layer). There is no Blade; the reuse primitives are PHP `Widget` classes (`Widget::widget()`), `$this->render()`/`renderPartial()` for partials, and `ActiveForm`/`GridView` framework widgets, not `@include`/`@component`.

## Summary

| Category | Count | Notes |
|---|---|---|
| `common/widgets/` (Yii2 `Widget` subclass) | 1 | `Alert.php` — flash-message renderer, effectively dead in the live app (see §1) |
| `common/components/` (Yii2 `Component` subclass) | 1 | `R2Storage.php` — Cloudflare R2 file storage + JSON-log service, widely used |
| Other `Widget` subclasses (outside `common/widgets/`) | 2 | `frontend/components/SupportNo.php`, `frontend/components/StepAndSupport.php` — despite the `components` folder name, both `extends \yii\base\Widget` |
| True partials (`$this->render()`/`renderPartial()` to a partial file) | 15 files render into | 2 admin layout-chrome partials (`top_bar`, `left_side_menu`) + 13 `_form.php` CRUD-form partials shared between create/update actions |
| De-facto repeated patterns (framework widget or markup copy-pasted per view, not extracted) | 9 | Select2 (kartik) init, `yii\jui\DatePicker` init, GridView admin-list shell, admin CRUD breadcrumb+card shell, manual flash-message block, plain `fileInput()`, `single_input__box`/`fieldConfig` wizard field wrapper, preloader overlay, R2-storage conditional image-echo (PDF/preview templates) |
| Confirmed-dead / non-functional patterns | 2 | `enableAjaxValidation` on frontend `ActiveForm` fields (JS never loads — see §3), one dead duplicate view file |

---

## 1. `common/widgets/Alert.php`

- **Path:** `common/widgets/Alert.php` (76 lines), `namespace common\widgets`, `extends \yii\bootstrap5\Widget`.
- **Purpose:** Stock Yii2-advanced-template flash-message widget — reads `Yii::$app->session->getAllFlashes()` and renders each as a `\yii\bootstrap5\Alert` (`alert-danger`/`alert-success`/`alert-info`/`alert-warning`), then clears the flash. Unmodified from the Yii2 advanced-template scaffold (doc comment still credits Kartik Visweswaran / Alexander Makarov).
- **Referenced (imported) in 2 layout files:**
  - `backend/views/layouts/main.php:7` (`use common\widgets\Alert;`) and called at `main.php:67` (`<?= Alert::widget() ?>`).
  - `frontend/views/layouts/mainNavy.php:6` (`use common\widgets\Alert;`) — **imported but never called.** `grep -n "Alert::widget" frontend/views/layouts/mainNavy.php` returns zero matches; `Breadcrumbs`, `Nav`, `NavBar` are imported in the same file and are equally unused. Dead scaffold imports left over from customizing the Yii2-basic-template default layout into the navy-branded one.
- **Effectively dead widget:** `backend/views/layouts/main.php` — the only file that actually calls `Alert::widget()` — is itself an unused leftover layout (see §4, Layout structure). The live default layouts are `admin` (backend, `backend/config/main.php:17`) and `mainNavy` (frontend, `frontend/config/main.php:21`); neither of those invokes `Alert::widget()`. Net effect: **flash messages set via `Yii::$app->session->setFlash()` (21 call sites across `frontend/controllers/` + `backend/controllers/`, confirmed via `grep -rl "setFlash"`) are never rendered by this widget anywhere in the running app.**
- **Actual flash-rendering mechanism used instead:** individual views hand-roll the flash check inline, e.g. `backend/views/sailors/_form.php:31-33`:
  ```php
  <?php if (Yii::$app->session->hasFlash('success')) : ?>
      <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
  <?php endif; ?>
  ```
  Confirmed present (via `grep -rln "getFlash"`) in 19 view files: `backend/views/{sailors,de-sailors,subjects,can-designation,upozilas}/_form.php`, `backend/views/sailors/reference/{reference_candidate,update_reference_candidate}.php`, `backend/views/de-sailors/reference/add_reference_candidate.php`, `backend/views/report/{monitoring_application,payment_report}.php`, `frontend/views/de-sailor/academic_info.php`, `frontend/views/de-sailor/candidate/{my_application,application_form_download}.php`, `frontend/views/my-application/index.php`, `frontend/views/candidate/{change_password,request_password_reset}.php`, `frontend/views/sailor-candidate/{payment,academic_info}.php`, `frontend/views/sailor-candidate/candidate/{application_form_download,my_application}.php` — this is the de-facto repeated flash-message pattern in this codebase (see §3), not the `Alert` widget.

---

## 2. `common/components/R2Storage.php`

- **Path:** `common/components/R2Storage.php` (217 lines), `namespace common\components`, `extends \yii\base\Component`.
- **Registered as app component** `r2Storage` in `common/config/main.php:19-27` (both `frontend` and `backend` inherit `common/config/main.php`):
  ```php
  'r2Storage' => [
      'class' => 'common\components\R2Storage',
      'accessKey' => '', 'secretKey' => '', 'endpoint' => '', 'fileUrl' => '',
      'bucket' => 'sailor-images', 'region' => 'auto', 'verifySsl' => false,
  ],
  ```
- **Purpose:** Wraps `Aws\S3\S3Client` to talk to **Cloudflare R2** (S3-compatible) for candidate photo/document storage, plus two side purposes bolted onto the same component: `uploadFile()`/`fileExists()`/`deleteFile()` for object storage, and `upsertCandidateLog()`/`actionLog()`/`getLogFileContents()` for an NDJSON-based audit-log file (`logs/logs.ndjson` in the same bucket) — candidate-data change log and route-action log, keyed by merging/upserting JSON lines rather than a DB table.
- **Callers (via `Yii::$app->r2Storage`), confirmed by grep across `frontend/`, `backend/`, `common/`):**
  - Controllers: `frontend/controllers/DeSailorController.php`, `frontend/controllers/SailorCandidateController.php`, `backend/controllers/SailorsController.php`, `backend/controllers/DeSailorsController.php`, `backend/controllers/LogReportController.php`, `backend/controllers/ReportController.php`.
  - Views (10 files) — mostly the `fileExists()`-gated conditional image-echo pattern documented in §3: `frontend/views/sailor-candidate/{personal_info,application_preview,application_verify_preview,candidate/application_form_pdf}.php`, `frontend/views/de-sailor/{personal_info,application_preview,application_verify_preview,candidate/application_form_pdf}.php`, `backend/views/sailors/_form.php`, `backend/views/de-sailors/_form.php`.

---

## 3. Other `Widget` subclasses (repo-wide grep for `extends \yii\base\Widget` / `extends Widget`)

Grep command: `grep -rln "extends \\\\yii\\\\base\\\\Widget\|extends Widget" --include='*.php' common frontend backend console` → exactly 2 hits, both under `frontend/components/` (a misleadingly-named directory — these are widgets, not Yii2 "components" in the app-component sense; their view templates correctly live in the sibling `frontend/components/views/` folder per Yii2 widget convention).

### `frontend\components\SupportNo`
- **Path:** `frontend/components/SupportNo.php` (18 lines) + view `frontend/components/views/support_no.php` (15 lines).
- **Purpose:** Static Bengali-language instructional banner ("fill the form entirely in English…") + support phone numbers/email, shown above wizard steps. Props: `$steps` (unused inside the view — dead prop, the view never reads it), `$slug` (also unused inside the view), `$show_form_text` (toggles the "fill in English" instruction + phone-number line order).
- **Used by (12 call sites, all `SupportNo::widget([...])`):** `frontend/views/de-sailor/{payment,application_preview,academic_info,personal_info}.php`, `frontend/views/sailor-candidate/{application_preview,academic_info,personal_info,payment}.php`, `frontend/views/check-eligibility/{personal_info,eligible_department,academic_info}.php`.
- **Dependencies:** None (pure static PHP/HTML, no JS).

### `frontend\components\StepAndSupport`
- **Path:** `frontend/components/StepAndSupport.php` (17 lines) + view `frontend/components/views/step_and_support.php` (52 lines).
- **Purpose:** 5-step progress indicator for the sailor/DE-sailor application wizard (Payment → Academic Information → Personal Information → Application Preview → Complete). Takes `$steps` (array of step numbers to mark `.active_step`) and `$slug` (accepted but never used inside the view — `<?php // $slug; ?>` at the top, dead prop).
- **Behavior:** Unlike the officer-portal's equivalent step indicator, **no step is ever clickable here** — every step's `<a href="#">` link is commented out in the markup (`step_and_support.php:8,17,26,36,45`), so this is purely a visual/read-only progress display, no back-navigation.
- **Used by (8 call sites, all `StepAndSupport::widget([...])`):** `frontend/views/de-sailor/{payment,academic_info,application_preview,personal_info}.php`, `frontend/views/sailor-candidate/{academic_info,application_preview,personal_info,payment}.php`.
- **Note:** `SupportNo` and `StepAndSupport` are always paired one call apart in the same 8 wizard views (e.g. `frontend/views/sailor-candidate/personal_info.php:16` then `:19`), mirroring the officer-portal's `support.blade.php` + `steps.blade.php` pairing — but here both are real `Widget` classes, not `@include` partials.
- **Dependencies:** None (pure server-rendered, no JS).

---

## 4. True partials (`$this->render()` / `$this->renderPartial()` to a shared partial file)

Grep: `grep -rn '\$this->render(' backend/views frontend/views` and the same against `backend/controllers frontend/controllers`.

### Layout-chrome partials
- `backend/views/layouts/top_bar.php` (68 lines) and `backend/views/layouts/left_side_menu.php` (183 lines) are rendered from `backend/views/layouts/admin.php:32-33` — `<?= $this->render('top_bar'); ?>` / `<?= $this->render('left_side_menu'); ?>` — the live default admin layout (see §5). Same reach model as the officer-portal's `left_menu`/`top_bar` Blade includes.
  - `left_side_menu.php` is a static nav tree (Sailor / DE Sailor / Sailor Setting / Sailor Report / DE Sailor Report / User), Bootstrap 5 `data-bs-toggle="collapse"` submenus. One hardcoded-ID gate: the "JSON For LS" report link is wrapped in `<?php if (!Yii::$app->user->isGuest && in_array(Yii::$app->user->id, [1])) { ?>` (`left_side_menu.php:144`) — same "hardcoded admin-user-ID allowlist" pattern flagged in the officer-portal doc, here scoped to a single user ID (`1`) instead of `[1,2,3]`.
  - `top_bar.php` shows a static avatar image (`adminAsset/images/users/avatar-1.jpg`, not the real user's photo) and a logout form — same pattern as officer-portal's top bar.

### CRUD `_form.php` partials
- **13 `_form.php` files**, one per backend CRUD module: `backend/views/{can-designation,de-sailors,districts,eligibility,sailor-batch-configuration,sailor-batchs,sailor-cent-dist-mapping,sailor-centers,sailors,subjects,unions,upozilas,user}/_form.php`.
- **Two different call conventions coexist:**
  1. **Controller-level render (the majority — 12 of 13 modules):** `actionCreate()` and `actionUpdate()` both `return $this->render('_form', ['model' => $model]);` directly from the controller, with **no separate `create.php`/`update.php` view** — e.g. `backend/controllers/DistrictsController.php:82-85` and `:103-105`; confirmed no `districts/create.php` or `districts/update.php` exists on disk (`ls backend/views/districts/` → only `_form.php`, `index.php`, `view.php`). The `_form.php` partial itself branches on `$model->isNewRecord` for the page title/submit label.
  2. **View-level render (1 module — `de-sailors`, the odd one out):** `backend/controllers/DeSailorsController.php:187` renders `update.php` (a thin Gii-scaffold-shaped wrapper — title + breadcrumbs), which then calls `$this->render('_form', ['model' => $model])` at `backend/views/de-sailors/update.php:19-21` — the only `$this->render('_form', …)` call that lives inside a *view* rather than a controller (confirmed: `grep -rn '\$this->render(' backend/views` returns exactly this one hit, vs. 24 controller-level hits across the other 12 modules).
- **`SailorsController.php:258`** has a **commented-out** `// return $this->render('_form', [...]);` — dead code left in place next to the live `_form` render at line 353.

---

## 5. Layout structure

| File | Assigned as default in | Renders / includes |
|---|---|---|
| `frontend/views/layouts/mainNavy.php` (257 lines) | `frontend/config/main.php:21` (`'layout' => 'mainNavy'`) — every `frontend/*` view | Header/nav/footer markup all inline (no `$this->render()` partials); registers `frontend\assets\AppNavyAsset`. Imports `common\widgets\Alert`/`Breadcrumbs`/`Nav`/`NavBar` but calls none of them (dead imports, see §1). |
| `backend/views/layouts/admin.php` (69 lines) | `backend/config/main.php:17` (`'layout'=>'admin'`) — every `backend/*` view except the login screen | `$this->render('top_bar')`, `$this->render('left_side_menu')` (§4); registers `backend\assets\AppAdminAsset` ("Hyper" Coderthemes admin theme). |
| `backend/views/layouts/top_bar.php` / `left_side_menu.php` | Partials only, not directly assigned | See §4. |
| `backend/views/layouts/blank.php` (33 lines) | `backend/controllers/SiteController.php:200` (`$this->layout = 'blank';`, `actionLogin()` only) | Chromeless shell — `<head>` + `$content` + `</body>`, no nav/footer. Registers `backend\assets\AppAsset` (a *second*, near-empty asset bundle distinct from `AppAdminAsset`). |
| `backend/views/layouts/main.php` (82 lines) | **Nothing.** No controller sets `layout = 'main'` and it isn't the configured default (`grep -rn "public \$layout" backend/controllers` → 0 hits; `backend/config/main.php` default is `admin`). Unused leftover from the stock Yii2-advanced-template scaffold. | `Alert::widget()`, `Breadcrumbs::widget()`, `NavBar::begin/end`, `Nav::widget()` — the only file in the repo that actually exercises these 4 stock widgets, and it's dead code. |

PDF-export views (`frontend/views/{de-sailor,sailor-candidate}/candidate/{application_form_pdf,application_verification_pdf}.php`, `backend/views/{report,de-sailor-report}/pdf/*.php`) are standalone `<!doctype html>` documents rendered outside any layout (captured to PDF), consistent with `view_inventory.md`'s note on the same files.

---

## 6. De-facto repeated UI patterns (framework-widget config or markup copy-pasted per view, not extracted)

### Select2 dropdown (`kartik-v/yii2-widget-select2`, installed per `composer.json:24`)
- **Extracted?** No — each `ActiveForm::field()->widget(Select2::classname(), [...])` call repeats its own `data`/`pluginOptions` inline.
- **Pages using it (13 files, via `grep -rl "kartik\\\\select2\\\\Select2"`):** `backend/views/eligibility/_form.php`, `backend/views/sailor-batch-configuration/_form.php`, `backend/views/sailor-cent-dist-mapping/_form.php`, `backend/views/log-report/report.php`, `backend/views/report/{same_academic_info,exam_date_check_by_center_designation,candidate_filter,district_candidate,center_date_candidate,center_candidate,json_for_ls}.php`, `backend/views/de-sailor-report/candidate_filter.php`, `frontend/views/check-eligibility/personal_info.php`.
- **Behavior:** e.g. `backend/views/eligibility/_form.php:159-166`:
  ```php
  echo $form->field($model, 'trade_course_subject')->widget(Select2::classname(), [
      'data' => ArrayHelper::map(Subjects::getAllActiveSubjectBySubjectType(...), 'id', 'name_en'),
      'value' => $model->trade_course_subject,
      'language' => 'en',
      'options' => ['multiple' => true, 'placeholder' => 'Select Trade'],
      'pluginOptions' => ['allowClear' => true],
  ]);
  ```
  Each of the 13 files re-supplies its own `data`/placeholder/`pluginOptions` rather than a shared config helper — same "same job, retyped every time" shape as the officer-portal's raw-jQuery Select2 init, just expressed through the kartik `ActiveField::widget()` API instead of a hand-written `.select2({...})` call.
- **Dependencies:** kartik-v/yii2-widget-select2 (registers its own JS/CSS via the widget, no manual `<script src>` needed).

### `yii\jui\DatePicker` (jQuery UI date picker, core Yii2 extension)
- **Extracted?** No — inline pattern, not extracted.
- **Purpose:** Date fields across admin CRUD forms and report filter forms (batch dates, exam dates, DOB, report date ranges).
- **Pages using it (26 files):** `backend/views/{eligibility,sailors,de-sailors,sailor-batchs,sailor-batch-configuration}/_form.php`, `backend/views/sailors/{index,reference/update_reference_candidate,reference/reference_candidate}.php`, `backend/views/de-sailors/reference/update_reference_candidate.php`, `backend/views/report/{same_academic_info,district_candidate,center_date_candidate,exam_date_check_by_center_designation,monitoring_application,payment_report,candidate_filter,json_for_ls,center_candidate}.php`, `backend/views/log-report/report.php`, `backend/views/de-sailor-report/{monitoring_application,candidate_filter,payment_report}.php`, `frontend/views/candidate/{request_password_reset,sign_up}.php`, `frontend/views/de-sailor/candidate/application_form_download.php`, `frontend/views/sailor-candidate/candidate/application_form_download.php`, `frontend/views/check-eligibility/personal_info.php`.
- **Note:** this is a *different* date-picker choice than the officer-portal (which used Flatpickr) — no Flatpickr anywhere in this repo (`grep -rli flatpickr frontend backend` → 0 hits).

### GridView admin-list shell (functional — unlike the officer-portal's dead DataTables shell)
- **Extracted?** No — every `index.php` repeats the same breadcrumb + card + `GridView::widget([...])` skeleton inline.
- **Pages using it (16 files, via `grep -rl "GridView::widget"`):** `backend/views/{subjects,can-designation,de-sailors,sailor-batch-configuration,upozilas,sailor-batchs,sailors,eligibility,districts,sailor-centers,user,unions,sailor-cent-dist-mapping}/index.php`, `backend/views/{sailors,de-sailors}/reference/reference_candidate.php`, plus a dead duplicate (see below).
- **Behavior — this one is actually wired up, real server-side data:** `ActiveDataProvider` + `$searchModel` filter form + real pagination (custom `pager` config with `previous`/`next` labels and Bootstrap `page-link`/`page-item` classes), e.g. `backend/views/districts/index.php:44-90`. `ActionColumn` templates vary: `{update}` only (`districts`, `de-sailors`, `sailor-cent-dist-mapping` reference lists), vs. `{update}&nbsp; {delete}` in 7 files (`can-designation`, `sailor-batch-configuration`, `sailor-batchs`, `subjects`, `sailor-centers`, `sailor-cent-dist-mapping`, `eligibility`) — and unlike the officer-portal's dead `.remove` click handler, `{delete}` here is a **live** Yii2 `ActionColumn` link wired to a real `actionDelete($id)` on the controller (confirmed on `SubjectsController.php:115-119`: `$this->findModel($id)->delete(); return $this->redirect(['index']);`).
- **Dead duplicate flagged:** `backend/views/de-sailors/reference/02092025_reference_candidate.php` (170 lines, a dated/backup copy of `reference_candidate.php`, 196 lines) is never rendered by any controller (`grep -rn "02092025_reference_candidate" backend/controllers` → 0 hits) — leftover dead file, same class of issue as the officer-portal's `__application_preview.blade.php` duplicate.
- **`Pjax` usage is inconsistent across the 16 GridView files:** all 16 `use yii\widgets\Pjax;`, but only **10 live files** actually wrap the grid in active `Pjax::begin() … Pjax::end()` (partial AJAX filter/sort/paginate without a full reload) — `eligibility/index.php:62-158`, `sailors/index.php:204-419`, `de-sailors/index.php:86-244`, `user/index.php:49-143`, `sailor-cent-dist-mapping/index.php:44-113`, `sailor-centers/index.php:43-83`, `unions/index.php:49-114`, `upozilas/index.php:47-105`, `sailors/reference/reference_candidate.php:96-236`, `de-sailors/reference/reference_candidate.php:79-176` (plus the dead `02092025_reference_candidate.php` duplicate, which also has it active). In the remaining **4 files** it's imported but deliberately commented out — `districts/index.php:45-93` (`<?php // Pjax::begin(); … // Pjax::end(); ?>`), `can-designation/index.php:44-95`, `sailor-batchs/index.php:45-96`, `sailor-batch-configuration/index.php:66-203` — so those 4 grids do a full page reload on filter/sort/paginate while the other 10 use Pjax partial refresh, an inconsistency across otherwise near-identical CRUD list pages.

### Admin CRUD page shell (breadcrumb + card wrapper)
- **Extracted?** No — inline pattern, not extracted; only the outer `layouts/admin.php` shell + its `top_bar`/`left_side_menu` partials are actually shared (§4).
- **Pages using it (46 of 72 non-layout backend views, via `grep -rl "breadcrumb bg-light-lighten"`):** every `index.php`/`_form.php`/report view under `backend/views/` — e.g. `backend/views/districts/index.php:19-26`:
  ```php
  <div class="row"><nav aria-label="breadcrumb"><ol class="breadcrumb bg-light-lighten p-2 mb-0">
      <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
      <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
      <li class="breadcrumb-item active" aria-current="page">Districts</li>
  </ol></nav></div>
  ```
  Same dead `href="#"` breadcrumb-link issue as the officer-portal.

### Manual flash-message block
- Already documented in full in §1 (19 files) — the de-facto replacement for the effectively-dead `Alert` widget.

### Plain `fileInput()` (no upload-preview widget)
- **Extracted?** No.
- **Pages using it (5 files, via `grep -rl "fileInput"`):** `backend/views/sailors/_form.php`, `backend/views/de-sailors/_form.php`, `backend/views/sailor-batchs/_form.php`, `frontend/views/sailor-candidate/personal_info.php`, `frontend/views/de-sailor/personal_info.php`.
- **Behavior:** e.g. `backend/views/sailors/_form.php:107`: `$form->field($model, 'photo')->fileInput(['maxlength' => true])` — bare Yii2 `ActiveField::fileInput()`, no `dropify`/`filepond`/preview plugin anywhere (`grep -rin "dropify|filepond"` → 0 hits, same as officer-portal). The frontend candidate-photo field additionally has ad-hoc client-side JS elsewhere in `personal_info.php` (`FileReader`/`#imgPreview`) for a live thumbnail preview — hand-rolled, not a shared widget.

### Frontend wizard field wrapper (`single_input__box` + shared `ActiveForm::fieldConfig`)
- **Extracted?** No — inline pattern, but here Yii2's own `ActiveForm::begin(['fieldConfig' => [...]])` already centralizes the label/error CSS classes *once per form* (unlike the officer-portal's Blade version, which repeats the wrapper markup on every single field).
- **Pages using it:** `single_input__box` div wrapper appears in `frontend/views/{sailor-candidate,de-sailor}/{personal_info,payment}.php` and `frontend/views/check-eligibility/personal_info.php` (5 files). The `'fieldConfig' => ['errorOptions' => ['class' => 'invalid-feedback bangla_font'], 'labelOptions' => [...]]` block is repeated per `ActiveForm::begin()` call in 8 files (`de-sailor/{academic_info,payment,personal_info}.php`, `sailor-candidate/{academic_info,payment,personal_info}.php`, `check-eligibility/{personal_info,academic_info}.php`) — same shape, retyped per file rather than a shared form-options helper.
- **Per-field `enableAjaxValidation => true` is set heavily** (81–85 occurrences each in `sailor-candidate/personal_info.php` and `de-sailor/personal_info.php`, per `grep -c enableAjaxValidation`) **but is dead configuration on the frontend app**: `frontend/assets/AppNavyAsset.php:29-32` has `$depends` fully commented out, so `yii.js`/`yii.activeForm.js` never loads on the public site (already established in `javascript_inventory.md:47`). This is why every AJAX-heavy frontend view hand-rolls its own jQuery `$.ajax()` instead of relying on Yii2's built-in ActiveForm AJAX validation — confirmed via 7 files with inline `$.ajax(` calls (`frontend/views/candidate/sign_up.php`, `de-sailor/personal_info.php`, `check-eligibility/{academic_info,personal_info,eligible_department}.php`, `sailor-candidate/{payment,personal_info}.php`), used for two different jobs: (a) cascading district→upazila→union dropdown population (`de-sailor/personal_info.php`, `check-eligibility/personal_info.php`, `sailor-candidate/personal_info.php` — hitting `ajax/upazial-by-district` / `ajax/union-by-upazial` routes), and (b) live field-availability checks on signup (`candidate/sign_up.php:129-132`). By contrast, `backend/assets/AppAdminAsset.php:26` **does** declare `'yii\web\YiiAsset'` in `$depends`, so ActiveForm client/AJAX validation and `ActionColumn` `{delete}` confirm dialogs are live on the admin side.

### Preloader / loading-spinner overlay
- **Extracted?** No — inline pattern, not extracted.
- **Pages using it (5 files, via `grep -rl 'id="preloader"'`):** `frontend/views/{sailor-candidate,de-sailor}/personal_info.php`, `backend/views/sailors/index.php`, `backend/views/{sailors,de-sailors}/reference/add_reference_candidate.php`.
- **Behavior:** Identical markup repeated per file (same shape as the officer-portal's version): fixed-position dim overlay + Bootstrap `spinner-grow`, toggled via jQuery `.show()`/`.hide()` from the inline `$.ajax()` `beforeSend`/`success`/`error` callbacks — e.g. `frontend/views/sailor-candidate/personal_info.php:986-1001` (the cascading-dropdown fetch documented above).

### R2-storage conditional image-echo (PDF/preview templates)
- **Extracted?** No — inline pattern, not extracted; each template repeats its own `if ($model->photo && Yii::$app->r2Storage->fileExists($model->photo)) { echo '<img src=' . Yii::$app->r2Storage->fileUrl . $model->photo . ...; }` block.
- **Pages using it (10 files, via `grep -rl "r2Storage->fileExists"`):** `frontend/views/{sailor-candidate,de-sailor}/{application_preview,application_verify_preview,candidate/application_form_pdf}.php`, `frontend/views/{sailor-candidate,de-sailor}/personal_info.php`, `backend/views/{sailors,de-sailors}/_form.php`.
- **Behavior:** e.g. `frontend/views/sailor-candidate/candidate/application_form_pdf.php:97-98`. This is the Cloudflare-R2-backed analogue of the officer-portal's local-vs-remote `genpdf` image-echo pattern — here the branch is an existence check against R2 rather than local-path-vs-URL, but the same "no shared image-render helper" shape.

---

## Evidence commands used

```
find common/widgets common/components -type f
grep -rln "extends \\\\yii\\\\base\\\\Widget\|extends Widget" --include='*.php' common frontend backend console   # 2 hits, both frontend/components/*.php
grep -rn "R2Storage" common/config backend/config                                                                 # component registration ('r2Storage' key)
grep -rln "R2Storage\|->r2Storage\|->r2storage" --include='*.php' frontend backend common console
grep -rn "SupportNo::widget\|StepAndSupport::widget" --include='*.php' frontend backend
grep -rln "widgets\\\\Alert\|Alert::widget" --include='*.php' frontend/views backend/views                       # 2 files, 1 actual call
grep -rln "getFlash" --include='*.php' frontend/views backend/views                                               # 19 files (de-facto flash pattern)
grep -rn '\$this->render(' backend/views frontend/views
grep -rn "return \$this->render('_form'" backend/controllers frontend/controllers                                # 24 hits across 12 modules
find backend/views/districts backend/views/de-sailors -type f                                                    # confirms create/update.php absence vs. presence
grep -rl "kartik\\\\select2\\\\Select2" backend/views frontend/views                                              # 13 files
grep -rl "yii\\\\jui\\\\DatePicker\|DatePicker::widget" backend/views frontend/views                              # 26 files
grep -rli flatpickr frontend backend                                                                              # 0 results
grep -rl "GridView::widget" backend/views frontend/views                                                          # 16 files
grep -rn "Pjax::begin" backend/views | grep -v "// Pjax::begin"                                                   # 11 active (10 live + 1 dead dup); 4 files have it commented out
grep -n "actionDelete" backend/controllers/SubjectsController.php                                                 # confirms live delete
grep -rn "02092025_reference_candidate" backend/controllers                                                       # 0 hits (dead file)
grep -rl "breadcrumb bg-light-lighten" backend/views                                                              # 46 files
grep -rin "dropify|filepond" frontend/views backend/views                                                         # 0 results
grep -rl "fileInput" backend/views frontend/views
grep -rl 'id="preloader"' frontend/views backend/views                                                            # 5 files
grep -c "enableAjaxValidation" frontend/views/*/*.php
grep -rl '\$\.ajax(' frontend/views                                                                               # 7 files
grep -rl "r2Storage->fileExists" frontend/views backend/views                                                     # 10 files
cat frontend/assets/AppNavyAsset.php backend/assets/AppAdminAsset.php                                             # confirms $depends difference (frontend has none loaded, backend loads YiiAsset)
```
