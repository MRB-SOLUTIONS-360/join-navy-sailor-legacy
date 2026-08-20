# Deep Dive — Admin: Bulk Reporting & Export (Sailor / DE-Sailor / Audit Log)

Scope: `backend/controllers/ReportController.php` (1,605 lines, 23 actions — the largest controller in the app), `backend/controllers/DeSailorReportController.php` (442 lines, 10 actions — the DE-Sailor mirror of `ReportController`), `backend/controllers/LogReportController.php` (265 lines, 2 actions — the ndjson audit-log viewer), plus `backend/models/Report.php` (the shared filter/scenario model both report controllers use) and the corresponding views in `backend/views/report/`, `backend/views/de-sailor-report/`, `backend/views/log-report/`.

All file paths below are repo-relative to `/home/bs-01692/Personal/MRB/Join Navy/Legacy/join-navy-sailor-legacy`. Base facts (action lists, route names, view-file existence) are cited from `docs/00-inventories/controller_inventory.md` and `docs/00-inventories/route_inventory.md`, which already inventoried these three controllers in Phase 0. Every field/column/format claim below comes from a direct read of the three controller source files and `backend/models/Report.php` (`ReportController.php` read in full across 4 passes: lines 1–450, 450–900, 900–1350, 1350–1605; `DeSailorReportController.php` and `LogReportController.php` each read in full in one pass — this controller's source was not separately detailed in `controller_inventory.md` beyond its action count, per the task brief, so it is documented here directly from source).

This repo is a Yii2 2.0 advanced-template app (`common/`, `frontend/`, `backend/`, `console/`), not Laravel — there is no route file to inventory the way the officer-legacy deep dive does; Yii2's default controller/action-id URL mapping means `report/payment` maps straight to `ReportController::actionPayment()` with no explicit route table. Route names cited below (`de-sailor-report/payment`, etc.) come from `route_inventory.md`'s direct read of the controller action IDs, not from a `routes/web.php`-style file.

---

## 0. Scope map — controllers → actions → views

| Controller | Actions | Route prefix | View directory |
|---|---|---|---|
| `ReportController.php` | 23 | `report/*` | `backend/views/report/`, `backend/views/report/pdf/` |
| `DeSailorReportController.php` | 10 | `de-sailor-report/*` | `backend/views/de-sailor-report/`, `backend/views/de-sailor-report/pdf/` |
| `LogReportController.php` | 2 | `log-report/*` | `backend/views/log-report/` |

**Access control: none, on any of the 35 actions across these 3 controllers.** None of the three defines a `behaviors()` method at all (confirmed by direct read — no `AccessControl`, no `VerbFilter`, not even a `beforeAction()` CSRF override). Per `docs/00-inventories/middleware_inventory.md` §3.2, these are 3 of the 5 backend controllers with zero behaviors override; they rely entirely on the app-level `'as access'` filter in `backend/config/main.php` (lines 132-144, `middleware_inventory.md` §1b). That app-level filter only checks `roles => ['@']` — **any authenticated user, regardless of `user_type`** — so any authenticated `common\models\User` record (not necessarily one whose `user_type` is `'admin'`) can hit every action documented below, including the raw PII bulk exports. `middleware_inventory.md` §4 point 3 confirms `user_type` is checked only once, at login time, never per-request. This is a real gap, not a documentation nicety: `ReportController::actionCandidateFilterExcel()`, `actionReferenceCandidateExcel()`, and `actionReferenceCandidateWord()` all decrypt and export candidate phone numbers, photos, names, districts, and academic results in bulk.

Every backend controller in this app inherits from `\yii\web\Controller` directly (no shared base controller — `controller_inventory.md` line 241), so there is no base-class hook either that could be closing this gap centrally.

---

## 1. `backend/models/Report.php` — the shared filter model

Both `ReportController` and `DeSailorReportController` build their filter forms around the same `backend\models\Report` model (a plain `yii\base\Model`, not an `ActiveRecord` — it has no table, it only exists to validate/hold filter POST data per request). It exposes scenario constants that gate which fields are `required` for a given report:

```php
// backend/models/Report.php:34-40
const PAYMENT_REPORT = 'payment_report';
const CANDIDATE_FILTER = 'candidate_filter';
const CANDIDATE_MONITORING_BY = 'monitoring_by';
const CANDIDATE_DISTRICT_WISE = 'district_wise';
const CANDIDATE_CENTER_WISE = 'center_wise';
const CANDIDATE_FILTER_WITH_SERIAL_NUMBER = 'candidate_filter_with_serial_number';
const CANDIDATE_CENTER_DATE_WISE = 'center_exam_date_wise';
```

`rules()` (lines 46-61) maps each scenario to its required fields — e.g. `PAYMENT_REPORT` requires `batch`, `payment_type`, `is_paid`; `CANDIDATE_FILTER` requires `batch`, `district`, `center`, `designation`; `CANDIDATE_DISTRICT_WISE` requires `batch`, `district`; `CANDIDATE_CENTER_WISE` requires `batch`, `center`; `CANDIDATE_CENTER_DATE_WISE` requires `batch`, `exam_date`, `center`. **`ReportController::actionCandidateFilter()` sets `$model->scenario` to a commented-out no-op** (`// $model->scenario = $model::CANDIDATE_FILTER;`, line 128) — unlike its `DeSailorReportController` counterpart (which does set the scenario, line 29 there) — so on the Sailor side the candidate-filter form's required-field validation never actually fires; only the default/no-scenario `safe` rules apply, meaning an admin can submit an entirely empty filter form and the query below will run with all-null `WHERE` clauses (batch/center/designation all `null`), a broader unfiltered dump than the DE-Sailor equivalent permits.

Also on `Report.php`: a static helper, `Report::getSscResult($teletalkData)` (line 89 onward), used only by `ReportController::actionCandidateFilterExcel()` — decodes a JSON blob of Bangladesh SSC-board Teletalk exam-result data (`ltrgd` grade-letter string) and maps subject codes to Bangla/English/Math/Physics/Biology grades, with separate code tables for madrasah vs. non-madrasah boards and a science/non-science branch.

---

## 2. Report family: Payment Reports

Both `ReportController` and `DeSailorReportController` implement an identical 3-action payment-report family, one filtering `Sailors`, the other `DeSailors`.

| Action (Sailor / DE-Sailor) | Format | Behavior |
|---|---|---|
| `actionPayment()` | HTML (form + result table) | POST-filters by `batch_id` + `payment_type` + `payment_status`, selecting `app_unique_id, candidate_designation, center_id, batch_id, exam_date, exam_group, serial_no, name, ref_id, validation_id, card_type, card_no, trans_date, payment_status`. Session-stores the result set under key `report` and the raw filter values under `filter_value` (`ReportController.php:36-55`, `DeSailorReportController.php:26-45`). Renders `payment_report`. |
| `actionPaymentPdf()` | **mPDF** | Reads the two session keys, feeds them into `renderPartial('pdf/payment_report_pdf', ...)`, wraps in `\Mpdf\Mpdf` with `curlAllowUnsafeSslRequests = true` and `debug = true` (Sailor variant leaves `debug` **on** in production — `ReportController.php:77`; the DE-Sailor variant has the same flag, also `true`, `DeSailorReportController.php:67`), outputs inline, `exit()`. |
| `actionPaymentExcel()` | **PhpSpreadsheet/xlsx — non-functional stub** | See below. |

**`actionPaymentExcel()` is a literal placeholder on both controllers.** Full body (identical logic on both — `ReportController.php:88-119`, `DeSailorReportController.php:78-109`):

```php
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World !');
```

It then saves that single-cell workbook to `@rootDirFilUpload/media/exportXls/{Sailor|De_Sailor}_Candidate_List_.xlsx`, streams it back with `Content-Disposition: attachment`, and deletes the temp file. The session-stored `report`/`filter_value` data (the actual payment report just generated by `actionPayment()`) is **never read or written into the sheet** — clicking "Export to Excel" on the payment report literally downloads a one-cell "Hello World !" spreadsheet. This is confirmed dead/incomplete functionality, not a hypothesis — both action bodies were read in full and contain no other `setCellValue` call before the save.

---

## 3. Report family: Candidate Filter / Search

The main ad-hoc candidate search+export tool, present on both controllers.

- **`ReportController::actionCandidateFilter()`** (`ReportController.php:124-164`): the richer of the two variants. Raises `memory_limit` to `1024M` up front (line 126) — a tell that this query/export path is known to be heavy. Two query modes: if only `serial_no` is given (no batch/center/district/designation), it does a plain `serial_no` lookup; otherwise it filters `Sailors` by `batch_id`, `center_id`, `eligible_district` (IN), `candidate_designation` (IN), forces `payment_status = PAYMENT_PAID`, and optionally filters `exam_date`, `gender`, `ssc_group`, `father_occupation` (`LIKE`), plus `serial_no IS NOT NULL`. Selected columns include `photo`, `permanent_phone`, `dob`, `age_according_to_circular`, `ssc_gpa`, `ssc_teletalk_data` — i.e. this endpoint's result set (session-stored under `report`) carries decryptable PII and exam-result payloads even before any export action runs.
- **`DeSailorReportController::actionCandidateFilter()`** (`DeSailorReportController.php:114-137`): same shape, filtering `DeSailors`, no serial-no-only short-circuit, no `ssc_group`/`father_occupation` filters (`DeSailors` has different academic fields — see §5).

Both feed the same `report`/`filter_value` session-storage pattern the payment report uses, and both have a `*Pdf()` sibling (mPDF, `pdf/candidate_filter_pdf` view) that is a straightforward render of the session data — no format-specific findings there.

### `actionCandidateFilterExcel()` — the one genuinely complete export

This is the most fully-built export in the whole reporting surface, on the Sailor side. `ReportController.php:203-383`:

- Embeds the org logo (`@rootDirFilUpload/media/main_logo.png`) as a `PhpOffice\PhpSpreadsheet\Worksheet\Drawing` at `D1`, plus a Bangla header row ("বাংলাদেশ নৌবাহিনী") and batch/center summary rows built from `SailorBatchs::getAllBatchSession()` / `SailorCenters::getAllCenterSession()`.
- 20-column header row (`SL` through `Photo`) including a full **per-subject SSC grade breakdown** (Bangla/English/Math/Science-Physics/Biology), derived by calling `Report::getSscResult($value['ssc_teletalk_data'])` per row and parsing the returned grade string (`substr($subject_grad, strrpos($subject_grad, ':') + 1)`, line 282).
- **Decrypts** `permanent_phone` per row via `DataEncryption::dataDecrypt()` (line 307) and writes it in cleartext into the spreadsheet cell.
- Embeds each candidate's uploaded exam photo as an inline `Drawing` per row (`Yii::getAlias('@rootDirFilUpload') . $value['photo']`, lines 311-337), wrapped in a `try/catch` whose `catch` block **dumps the full candidate row plus the exception message via `print_r()` and calls `die()`** (lines 326-337) — i.e. a single bad/corrupt photo file for any one candidate in the result set aborts the entire export mid-stream with a raw PHP error dump containing that candidate's PII, rather than skipping the image and continuing.
- The DE-Sailor equivalent (`DeSailorReportController::actionCandidateFilterExcel()`, lines 171-268) is materially simpler: no logo, no SSC grade breakdown, a 10-column layout (SL/Application ID/Designation/District/Name/Gender/Phone/Serial No/Exam Date/Photo), same decrypt-and-embed pattern for phone and photo, no `try/catch` around the drawing (an unhandled photo-file error here would 500 rather than dump-and-die).

---

## 4. Report family: Monitoring / Photo-Check

`actionMonitoringApplication()` exists on both controllers, gated by `Report::CANDIDATE_MONITORING_BY` scenario (`monitor_by`, `batch`, `create_date` all required). Only one `monitor_by` value is actually handled — `Constants::CAN_MONITOR_BY_IMAGE_MISSING` — everything else silently returns an empty result set (no `else` branch).

- **Sailor variant** (`ReportController.php:389-432`): raises `memory_limit` to unlimited (`-1`) and turns on `error_reporting(E_ALL)`/`display_errors` up front (lines 392-393) — production-inappropriate debug flags left enabled on a live report action. Queries `Sailors` for the given batch/create-date with `serial_no IS NOT NULL`, `exam_date IS NOT NULL`, and (a later addition) `is_image_exist_check IS NULL` — an optimization column meant to cache "we already confirmed this candidate's photo exists" so repeat runs skip re-checking. For each candidate missing that cache flag, it checks photo existence via `Yii::$app->r2Storage->fileExists($val['photo'])` (Cloudflare R2 check, not local disk — note this differs from the DE-Sailor variant below). If the file **does** exist, it writes `is_image_exist_check = 1` back via a **raw, unparameterized SQL string** built with string concatenation of the row's numeric `id` (`"UPDATE ".$table." set is_image_exist_check=1 where id =". $val['id'].""`, lines 421-422) — `id` here comes from the DB itself (not user input) so it isn't an exploitable SQL-injection path in practice, but it is a raw-SQL write executed directly from a GET-reachable report action, bypassing the ActiveRecord layer entirely.
- **DE-Sailor variant** (`DeSailorReportController.php:274-301`): simpler — no `is_image_exist_check` caching column, no raw SQL, checks photo existence via plain `file_exists(Yii::getAlias('@rootDirFilUpload') . $val['photo'])` (local filesystem, not R2) rather than the Sailor variant's R2-aware check. This is a real behavioral divergence: if sailor photos have since migrated fully to R2 and local files were cleaned up, this DE-Sailor check would false-positive every candidate as "missing photo" even when the file exists remotely — worth flagging as a likely latent bug given the sibling controller was updated to use R2 and this one wasn't.

Both render `monitoring_application`.

---

## 5. Report family: Reference-Candidate Exports (PDF / Excel / Word)

"Reference candidates" are candidates who supplied a referrer (`have_reference = Constants::YES`) — used for a manual verification/interview workflow. This family is the most format-diverse: **mPDF, xlsx via PhpSpreadsheet, and docx via PhpWord all exist for the same underlying dataset**, and none of the three actions is reachable directly by URL with query parameters — every one of them instead reads a `reference_candidate_query_param` session key that must have been populated by an earlier, separate controller action (`SailorsController::actionReferenceCandidate()` / `DeSailorsController::actionReferenceCandidate()`, per `controller_inventory.md` lines 306, 410) before any of these exports will produce output. Hitting `report/reference-candidate-pdf` (etc.) cold, with no prior session state, either `die()`s with a raw string or silently redirects with a flash error, depending on which action and how recently it was touched up (see below).

| Action | Controller(s) | Format | Guard behavior |
|---|---|---|---|
| `actionReferenceCandidatePdf()` | Report (Sailor) | mPDF | **Recently hardened**: now checks `center_id` + `exam_date` are set in the session query params and, if not, flashes `'Please select exam date & center.'` and redirects back (`ReportController.php:585-588`) — the old `die('You must have select eligible district & Batch')` guard is left commented out just above it (lines 591-600), a visible trace of the guard being rewritten. If the query returns zero rows, it now renders `<h1>No Data Found</h1>` inside the PDF instead of failing (lines 633-636) — also a recent-looking improvement over the DE-Sailor sibling. |
| `actionReferenceCandidatePdf()` | DeSailorReport (DE-Sailor) | mPDF | **Older/unhardened**: still uses the raw `die('You must have select eligible district & Batch')` guard (`DeSailorReportController.php:312-314, 319`) if `eligible_district`/`batch_id` aren't in the session params — a bare `die()` with a developer-facing string, no flash message, no redirect, for what is a routine "you forgot to pick a filter" user error. |
| `actionReferenceDeCandidatePdf()` | Report (Sailor) | mPDF | Sources from `DeSailorsSearch` session params instead of `SailorsSearch` — lets a Sailor-context admin pull a DE-Sailor reference-candidate PDF from the same controller. Still uses the old `die()` guard pattern (`ReportController.php:651-659`), **not** the hardened flash/redirect pattern used by its own sibling `actionReferenceCandidatePdf()` two methods above — the fix was applied inconsistently within the same controller. |
| `actionReferenceDeCandidatePdf()` | DeSailorReport | mPDF | Same `die()` guard pattern, mirrors `DeSailorReportController::actionReferenceCandidatePdf()`. |
| `actionReferenceCandidateExcel()` | Report (Sailor) | **PhpSpreadsheet/xlsx — fully built** | See below. |
| `actionReferenceCandidateExcel()` | DeSailorReport (DE-Sailor) | **PhpSpreadsheet/xlsx — near-stub** | Writes only `$sheet->setCellValue('C2', 'We are working')` (`DeSailorReportController.php:419`) plus the real query/filename/download plumbing around it — the DE-Sailor reference-candidate Excel export produces a one-cell "We are working" placeholder file, the DE-Sailor counterpart to the Sailor side's `actionPaymentExcel()` stub. |
| `actionAllReferenceCandidateExcel()` | Report (Sailor) only | xlsx | A second, broader reference-candidate export (no DE-Sailor equivalent) — see below. |
| `actionReferenceCandidateWord()` | Report (Sailor) only | **PhpWord/docx — the only Word export in the app** | See below. |

### `ReportController::actionReferenceCandidateExcel()` (lines 744-915) — fully functional

Guards on `center_id` + `exam_date` (flash + redirect, the hardened pattern). Builds a landscape-style summary sheet: branch/designation line, center name, a `DATE {exam date} - TOTAL APPLICANT - {count}` header row (count computed via a live `Sailors::find()->count()` scoped to batch/center/exam_date/`exam_group IS NOT NULL`/`application_status=1`). Per-row columns: SL, Roll (`serial_no`), Mobile (`DataEncryption::dataDecrypt($value['permanent_phone'])` — decrypted phone written in cleartext, same pattern as §3), District (resolved via `Districts::getAllActiveDistrictBySlug()`), a multi-line "Description" cell (name/father-name/district/GPA, built by protected helper `getDescription()`), "Reference" and "Relationship" cells (JSON-decoded from the `referred_by`/`relationship` columns via protected helpers `getReference()`/`getRelation()`), a static "Subject" placeholder text block (`"B-\nE-\nM-\nSc-\nGk-"`, i.e. blank subject-score lines meant to be filled in by hand during the in-person interview), and an embedded photo per row — sourced via `Yii::$app->r2Storage->fileUrl . $value['photo']` (a public R2 URL, not a local file path — this is the one image-embedding action in the file that reads from R2 rather than local disk, `line 881`).

### `ReportController::actionAllReferenceCandidateExcel()` (lines 451-573) — the broader variant

Requires only `center_id` (not `exam_date`) in the session params — i.e. this pulls **all** reference candidates for a center across every exam date, not one date's cohort, hence "All" in the name. Same description/reference/relationship helper reuse, no photo embedding (columns are SL/Roll/Mobile/Designation/District/Description/Reference/Relationship — 8 columns, no photo column at all, unlike its date-scoped sibling). Wrapped in a `try/catch` around the whole body; on exception, flashes the message and redirects to the referrer — the only action in either controller with that kind of broad exception guard. Ends with an unreachable `die('This is not available now.')` after the `try/catch` (line 572) — dead code, since every code path inside the `try` block either `exit()`s (success) or is caught and redirects (failure); the `die()` can only ever be reached if `$searchModel` is falsy *and* no exception was thrown, i.e. "zero results, cleanly" — in which case the user sees a raw "This is not available now." string dump rather than a flash message, the one code path the `try/catch` doesn't cover.

### `ReportController::actionReferenceCandidateWord()` (lines 1125-1425) — docx, PhpWord

The only Word-document export anywhere in the codebase — cross-referenced against `controller_inventory.md`'s finding that `backend/controllers/TeController.php` (an empty stub) imports `PhpOffice\PhpWord\{PhpWord, IOFactory}` but never uses them; this action is the feature that stub appears to have been scaffolding for. Same `center_id`+`exam_date` guard, same underlying `Sailors` reference-candidate query and `SailorBatchConfiguration` quota lookup as the PDF/Excel siblings. Builds a landscape A4-ish `PhpWord\Section` (`marginTop`/`marginLeft`/etc. via `Converter::pointToTwip`), writes three centered header lines (branch, center name uppercased, `DATE ... - TOTAL APPLICANT - N`), then a bordered table with columns Ser/Roll & Mobile/District/Description/Reference/Relationship/Subject/Picture. Per-row: same decrypted-phone-in-plaintext pattern (`DataEncryption::dataDecrypt($val['permanent_phone'])`, line 1360), same static blank-subject-line placeholder text, and an embedded photo via `$table->addCell(2000)->addImage(Yii::$app->r2Storage->fileUrl . $val['photo'], ['height' => 65])` when the R2 file exists (line 1395-1401) — falls back to an empty cell otherwise. Streamed via `IOFactory::createWriter($phpWord, 'Word2007')->save('php://output')` with `Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document`, then `exit`.

Three private-looking formatting helpers are shared across this whole reference-candidate family (`protected`, not `private`, so technically inheritable): `getBranch()` (splits a designation label on `(` to reformat it), `getDescription()`, `getReference()`, `getRelation()` (`ReportController.php:694-739`) — all reused verbatim by `actionAllReferenceCandidateExcel()`, `actionReferenceCandidateExcel()`, and inlined (copy-pasted, not called) a second time inside `actionReferenceCandidateWord()` (lines 1320-1351) rather than reusing the same helper methods — the Word export duplicates ~30 lines of logic that already exists as `getDescription()`/`getReference()`/`getRelation()` just above it in the same file.

---

## 6. Report family: District / Center / Exam-Date Candidate-Count Breakdowns

A family of 3 parallel "how many candidates per designation, sliced by X" reports — District-wise, Center+Date-wise, Center-wise — each with a filter action, a PDF export, and (for District and Center) an Excel export. All are Sailor-only; **`DeSailorReportController` has no equivalent of any of these three** — DE-Sailor candidates get no district/center-breakdown reporting at all.

| Filter action | Group-by scope | Excel? | PDF |
|---|---|---|---|
| `actionDistrictCandidate()` (`ReportController.php:920-948`) | `batch` + optional `center`, `district` IN-list, `payment_status=PAID`, `application_status=ACTIVE`, `serial_no NOT NULL` | `actionDistrictCandidateExcel()` (1037-1118) | `actionDistrictCandidatePdf()` (1012-1032) |
| `actionCenterDateCandidate()` (952-982) | `batch` + optional `center`, exact `exam_date` | none | `actionExamDateCenterCandidatePdf()` (988-1008) |
| `actionCenterCandidate()` (1428-1454) | `batch` + optional `center`, optional `exam_date`, `payment_status=PAID`, `application_status=ACTIVE` | `actionCenterCandidateExcel()` (1482-1563) | `actionCenterCandidatePdf()` (1456-1476) |

All three filter actions run the same shape of query: `SELECT candidate_designation, COUNT(*) AS candidate_count FROM sailors WHERE ... GROUP BY candidate_designation`, session-stored under `report`/`filter_value` exactly like the payment/candidate-filter families, then rendered by their own view + optional PDF/Excel pair reading that same session state. The two Excel exports (`actionDistrictCandidateExcel()`, `actionCenterCandidateExcel()`) are near-identical: logo drawing, Bangla batch/district-or-center header, a 3-column SL/Designation/Total-Candidate table with a running `$total` summed across rows and written as a final "Total" row. `actionCenterDateCandidate()` is the one filter in this family with **no Excel export at all** — only PDF.

Two small inconsistencies worth noting: `actionDistrictCandidate()` filters `application_status = Constants::STATUS_ACTIVE` but `actionCenterDateCandidate()` does not (it has no `application_status` filter at all, meaning cancelled applications are still counted in that one report) — three near-identical count-reports, three slightly different active/paid filter combinations, not obviously intentional.

---

## 7. Stubs / render-only / incomplete actions

Beyond the two Excel "Hello World"/"We are working" placeholders already covered in §2/§5, three more actions in `ReportController.php` are incomplete:

- **`actionExamDateCheck()`** (`ReportController.php:435-445`): builds a `yii\base\DynamicModel` with fields `batch`, `center_id`, `create_date`, `candidate_designation`, `district_slug`, all marked `required`, and renders `exam_date_check_by_center_designation` — **but there is no `isPost` branch, no query, no data assembly at all**. The action only ever constructs the empty form and hands it to the view; whatever filtering logic this report is meant to run must live entirely client-side/AJAX (not present in this file) or was never finished. `$sailor_model = []` and `$exam_dates = []` are initialized (lines 437-438) and passed to the view unchanged.
- **`actionSameAcademicInfo()`** (`ReportController.php:1566-1576`): pre-fills `$model->exam_date = date('Y-m-d')` and renders `same_academic_info` with an empty `$sailor_model` — same pattern, no server-side query logic in the action body at all.
- **`actionJsonForLs()`** (`ReportController.php:1589-1604`): gated by a **hardcoded single-user check**, not role-based:
  ```php
  if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->id, [1])){
      return Yii::$app->response->redirect(Yii::$app->homeUrl);
  }
  ```
  This is the *only* access check anywhere in these three controllers, and it's a literal `user->id == 1` allow-list rather than a role/permission check — brittle (breaks the moment user id 1 is deactivated or a second admin needs this report) and inconsistent with every other action in the file having zero gating at all. Past the guard, it renders `json_for_ls` with three empty arrays (`$sailor_model`, `$sailor_model_district_wise_count`, `$returnData`) — render-only, same incomplete pattern as the two actions above.

Also dead: `protected function folderTurncate()` (`ReportController.php:1579-1587`, note the misspelling — "Turncate" not "Truncate") deletes every file in `@webroot/district_wise/` but is never called anywhere in the class (confirmed by `controller_inventory.md` line 52 and re-confirmed here by reading the full file — no call site exists).

---

## 8. `LogReportController.php` — the ndjson audit-log viewer

Full detail already captured in `controller_inventory.md` (lines 326-337) and `middleware_inventory.md` §1c; re-confirmed here by direct read since it's part of this deep dive's scope, but not re-derived from scratch.

This controller is the read side of the global action-logging mechanism registered in `backend/config/main.php`'s `'on beforeRequest'` event listener (`middleware_inventory.md` §1c) — every POST to a backend controller gets appended as an ndjson line to `action_log/{controller_id}/add.ndjson` or `update.ndjson` (or a date-stamped file for GET requests) via `Yii::$app->r2Storage->actionLog()`, and `LogReportController` reads those files back out.

- **`actionSiteActivity()`** (`LogReportController.php:14-74`): builds a `DynamicModel(['data', 'method', 'controller'])` with `method`/`controller` required. On POST, resolves which ndjson file to read — `insert`/`update` methods map to the fixed `add.ndjson`/`update.ndjson` files (POST-only actions like create/update forms); any other `$method` value is treated as an HTTP verb and maps to a **date-stamped** file, `{controller}/{Y-m-d}_{method}.ndjson`, defaulting the date to today if none given (line 30). Reads the file via `Yii::$app->r2Storage->getLogFileContents()`, decodes each ndjson line, and groups entries by `update_id` — **excluding any entry whose route is `site/login`** (line 45), i.e. login events are deliberately filtered out of this audit view. A hardcoded `$controller_list` array (lines 52-67) maps 12 controller-id slugs (`sailors`, `de-sailors`, `sailor-batchs`, `districts`, etc., plus `report`) to display labels for the filter dropdown — this list would need manual updates if a new loggable controller is added, since nothing generates it dynamically.
- **`actionSiteActivityView($date, $route, $method, $controller, $update_id)`** (lines 83-211): the modal-detail endpoint. Re-reads the same ndjson source, filters to entries matching the given `route` + `update_id`, then **manually builds an HTML `<table>` via `ob_start()`/raw `echo` calls** and returns the buffered string directly (not a `render()` call — there is deliberately no view file for this, confirmed present in `controller_inventory.md`'s view list as "not a render() call, no view file expected"). For each pair of consecutive log entries sharing an `update_id`, it computes a field-level diff via the private recursive helper `collectChanges($old, $new, $path)` (lines 222-264) — a dot-notated-path array diff (`['a.b' => ['old'=>..., 'new'=>...]]`) that recurses through nested arrays and flags added/removed/changed keys. Note: the two inline `old`/`new` key-name comments in `collectChanges()` are inverted relative to the actual assignment (e.g. line 242 assigns `'new' => $old[$key], 'old' => $new[$key]` — the variable named `$old` is stored under the `'new'` key and vice versa) — the comment above it even flags the "correct" ordering as commented-out (`// $changes[$subPath] = ['old' => $old[$key], 'new' => $new[$key]];`), suggesting this was a deliberate late swap, not an oversight; whether "old"/"new" ends up displaying correctly in the rendered modal table depends on the calling convention at `actionSiteActivityView()`'s call site (line 183, `$this->collectChanges($runningIndexData, $nextIndexData)`) matching this inversion — not independently verified against a live log sample, flagged for whoever owns this page to sanity-check against real data.

No AccessControl here either — same gap as `ReportController`/`DeSailorReportController`, and arguably more sensitive in a different way: this controller exposes a full field-level audit trail (who changed what, when, from what IP) for every admin-mutated record in the system to any authenticated user.

---

## 9. Export-format summary matrix

| Format | Library | Where used | Status |
|---|---|---|---|
| **mPDF** | `\Mpdf\Mpdf` (fully-qualified, not imported via `use`) | `actionPaymentPdf()` (both controllers), `actionCandidateFilterPdf()` (both), `actionReferenceCandidatePdf()` / `actionReferenceDeCandidatePdf()` (both), `actionExamDateCenterCandidatePdf()`, `actionDistrictCandidatePdf()`, `actionCenterCandidatePdf()` (Sailor only) | Functional throughout. Every mPDF instance sets `curlAllowUnsafeSslRequests = true` (fetches remote assets over SSL without verifying certs — consistent with the pattern already flagged for `frontend/controllers/DeSailorController.php` in `controller_inventory.md`). Several leave `$mpdf->debug = true` in what reads as production code (`actionPaymentPdf()` on both controllers). |
| **PhpSpreadsheet / xlsx** | `PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx, Style\Alignment, Worksheet\Drawing}` | `actionPaymentExcel()` (both — **stub**), `actionCandidateFilterExcel()` (both — Sailor fully built, DE-Sailor simpler but functional), `actionAllReferenceCandidateExcel()`, `actionReferenceCandidateExcel()` (Sailor fully built; DE-Sailor — **stub**), `actionDistrictCandidateExcel()`, `actionCenterCandidateExcel()` | Mixed — 2 of 6 distinct xlsx-producing actions are literal one-cell placeholders (`actionPaymentExcel()` ×2, `DeSailorReportController::actionReferenceCandidateExcel()`); the rest are complete, photo-embedding, Bangla-labeled exports. |
| **PhpWord / docx** | `PhpOffice\PhpWord\{PhpWord, IOFactory}` | `actionReferenceCandidateWord()` — the only docx export in the entire application | Functional. The only other place these classes are imported is the dead `backend/controllers/TeController.php` stub (`controller_inventory.md` line 49), which never got past scaffolding — this action is the feature that stub was presumably meant to build before `ReportController` got it instead. |
| Raw ndjson / HTML table | none (manual `echo`) | `LogReportController::actionSiteActivityView()` | Functional, not an export per se — server-rendered HTML fragment for a modal. |

**Every export in this file writes its temporary file to `@rootDirFilUpload/media/exportXls/` using a filename that is either static (`Sailor_Candidate_List_.xlsx`, `De_Sailor_Candidate_List_.xlsx` — no per-request uniqueness) or time-suffixed (`'reference_' . time()`, `'district_wise_candidate_' . time()`) — the static-name ones are a latent race condition under concurrent admin usage (two admins exporting the payment report at the same moment would write/read/delete the same file path), though low-severity given how small the observed admin user base is likely to be for this system.**

---

## 10. Findings summary

| # | Finding | Location | Severity |
|---|---|---|---|
| 1 | Zero `AccessControl` on all 35 actions across `ReportController`, `DeSailorReportController`, `LogReportController` — bulk PII export and full audit-trail viewing are reachable by any authenticated user regardless of `user_type`, not admin-gated | all three controllers (`middleware_inventory.md` §3.2, §1b) | High |
| 2 | `actionPaymentExcel()` is a non-functional "Hello World !" stub on both `ReportController` and `DeSailorReportController` | `ReportController.php:88-119`, `DeSailorReportController.php:78-109` | Medium (feature gap) |
| 3 | `DeSailorReportController::actionReferenceCandidateExcel()` is a "We are working" placeholder — real query/filename plumbing exists but no data is written | `DeSailorReportController.php:404-441` | Medium (feature gap) |
| 4 | `actionExamDateCheck()`, `actionSameAcademicInfo()`, `actionJsonForLs()` are render-only — form/page renders with no server-side query logic | `ReportController.php:435-445, 1566-1576, 1589-1604` | Medium (feature gap) |
| 5 | `actionJsonForLs()` gated by hardcoded `user->id == 1` allow-list, not role-based | `ReportController.php:1592` | Medium (fragile access control, inconsistent with rest of file) |
| 6 | `ReportController::actionCandidateFilter()` never sets its model scenario (commented out), so required-field validation never fires — an empty filter form runs an unfiltered query | `ReportController.php:128` | Medium |
| 7 | Decrypted candidate phone numbers written in cleartext into xlsx/docx exports (`DataEncryption::dataDecrypt()` called inline before `setCellValue`/`addText`) | `ReportController.php:307, 863, 1360`; `DeSailorReportController.php:227` | High (PII handling, compounds with finding #1) |
| 8 | `DeSailorReportController::actionMonitoringApplication()` checks photo existence via local `file_exists()` while the Sailor sibling checks via `r2Storage->fileExists()` — likely stale/incorrect if DE-Sailor photos have also migrated to R2 | `DeSailorReportController.php:292` vs. `ReportController.php:418` | Medium (latent bug, not independently reproduced) |
| 9 | Photo-embedding `try/catch` in `actionCandidateFilterExcel()` dumps full candidate PII + exception message via `print_r()`/`die()` on any single bad image file, aborting the whole export | `ReportController.php:326-337` | Medium |
| 10 | `actionAllReferenceCandidateExcel()` has an unreachable/near-unreachable `die('This is not available now.')` after a `try/catch` that already handles the only realistic failure path | `ReportController.php:568-572` | Low |
| 11 | `getDescription()`/`getReference()`/`getRelation()` helper logic is duplicated (copy-pasted, not reused) inside `actionReferenceCandidateWord()` instead of calling the existing protected methods | `ReportController.php:1320-1351` vs. `694-739` | Low (maintainability) |
| 12 | `folderTurncate()` (misspelled) is dead code — defined, never called | `ReportController.php:1579-1587` | Low |
| 13 | Static (non-unique) temp-export filenames for `actionPaymentExcel()` — race condition under concurrent use | `ReportController.php:99-101`, `DeSailorReportController.php:89-91` | Low |
| 14 | `LogReportController::collectChanges()` stores `$old`/`$new` under swapped dictionary keys (`'new' => $old[$key], 'old' => $new[$key]`) — deliberate-looking but unverified against real log output | `LogReportController.php:242, 248, 252, 261` | Low (unverified, needs a live-data check) |
| 15 | Inconsistent `application_status`/`payment_status` filter combinations across the three near-identical district/center/date candidate-count reports | `ReportController.php:920-948, 952-982, 1428-1454` | Low |
