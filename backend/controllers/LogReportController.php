<?php

namespace backend\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\web\Response;

class LogReportController extends \yii\web\Controller
{
    /**
     * Log Report 
     */
    public function actionSiteActivity()
    {
        $model = new DynamicModel(['data', 'method','controller']);
        $model->addRule(['method','controller'], 'required');

        

        $processedData = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            $postMethod = $model->method;
            if (strtolower($postMethod) === 'insert' || strtolower($postMethod) === 'update') {
                if (strtolower($postMethod) === 'insert') $logFile = 'action_log/'.$model->controller.'/add.ndjson';
                else $logFile = 'action_log/'.$model->controller.'/update.ndjson';
                $postMethod = 'POST';
            } else {
                $model->data = $model->data ?? date('Y-m-d');
                $logFile = 'action_log/'.$model->controller . '/' . date('Y-m-d', strtotime($model->data)) . '_' . strtolower($postMethod) . '.ndjson';
            }

            $fileContent = Yii::$app->r2Storage->getLogFileContents(logFile: $logFile);

            // Split lines and decode JSON in one step, skipping empty lines
            $allData = array_filter(array_map(fn($line) => json_decode(trim($line), true), explode("\n", $fileContent)));

            // Organize data by route
            foreach ($allData as $dataBlock) {
                foreach ($dataBlock as $entry) {
                    $route = $entry['route'] ?? 'unknown';
                    $method = $entry['method'] ?? 'unknown';
                    $update_id = $entry['update_id'] ?? 'unknown';
                    if (strtolower($method) === strtolower($postMethod) && $route != 'site/login') {
                        $processedData[$update_id][] = $entry;
                    }
                }
            }
        }

        $controller_list=[
            'sailors'=>'Sailor',
            'de-sailors'=>'DE-Sailor',
            'sailor-batchs'=>'Sailor Batch',
            'sailor-batch-configuration'=>'Sailor Batch Configuration',
            'districts'=>'District',
            'upozilas'=>'Upozila',
            'unions'=>'Union',
            'subjects'=>'Subject',
            'can-designations'=>'CAN Designation',
            'eligibility'=>'Eligibility',
            'sailor-centers'=>'Sailor Center',
            'sailor-cent-dist-mapping'=>'Sailor Center District Mapping',
            
            'report'=>'Report'        
        ];

        return $this->render('report', [
            'model' => $model,
            'processedData' => $processedData,
            'controller_list'=>$controller_list
        ]);
    }

    /**
     * Returns detailed log entries for a specific route on a given date.
     * This is used by the modal in the Site Activity view.
     * @param string $date Y-m-d date string
     * @param string $route Route identifier
     * @return string HTML content
     */
    public function actionSiteActivityView($date, $route, $method, $controller, $update_id)
    {
        $method = strtolower($method);
        $controller = strtolower($controller);
        if ($method === 'insert' || $method === 'update') {
            if ($method === 'insert') $logFile = 'action_log/'.$controller.'/add.ndjson';
            else $logFile = 'action_log/'.$controller.'/update.ndjson';
            $method = 'POST';
        } else {
            $logFile = 'action_log/'.$controller . '/' . date('Y-m-d', strtotime($date)) . '_' . $method . '.ndjson';
        }      

      


        $fileContent = Yii::$app->r2Storage->getLogFileContents(logFile: $logFile);

        $allData = array_filter(array_map(function ($line) {
            return json_decode(trim($line), true);
        }, explode("\n", $fileContent)));

        $entries = [];
        $entriesIdWise = [];
        foreach ($allData as $dataBlock) {
            foreach ($dataBlock as $entry) {
               
                $r = $entry['route'] ?? 'unknown';

              
                if ($r == $route && $entry['update_id'] == $update_id) {
                 
                    $entries[] = $entry;
                    $entriesIdWise[$entry['update_id']][] = $entry;
                }
            }
        }
        Yii::$app->response->format = Response::FORMAT_HTML;      
   
        ob_start();
        // Inline styles to improve readability inside modal
        echo '<style>
            
            .pre-json { white-space: pre-wrap; max-height: 250px; overflow: auto; font-size: 12px; background: #f6f8fa; padding: 8px; border-radius: 4px; }
            .cell-narrow1 { width: 60px; }
            .cell-narrow { width: 120px; }
            .cell-wide { min-width: 240px; }
            /* Sticky header */
            .table.table-fixed thead th { position: sticky; top: 0; background:rgba(245, 237, 237, 0.67); z-index: 2; }
            .table.table-fixed thead th::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 1px; background: rgba(0,0,0,0.1); }
        </style>';
        echo '<div class="table-responsive" style="max-height: 70vh; overflow: auto;">';
        echo '<table class="table table-sm table-hover align-middle table-bordered table-fixed">';
        // Single header for all entries
        echo '<thead><tr>
                <th class="cell-narrow1">#</th>
                <th class="cell-narrow">Update ID</th>
                <th class="cell-narrow">Time</th>
                <th class="cell-narrow">User</th>
                <th class="cell-narrow">IP</th>
                <th class="cell-wide">Data</th>
                <th class="cell-wide">Changed</th>
              </tr></thead>';
        echo '<tbody>';

        // Flatten all grouped entries but keep their group (update_id) shown in a column
        $rowIndex = 0;
        foreach ($entriesIdWise as $groupId => $groupEntries) {
            foreach ($groupEntries as $i => $e) {
                $rowIndex++;
                $ts = isset($e['timestamp']) ? date('Y-m-d h:i:s A', strtotime($e['timestamp'])) : '';
                $user = $e['data']['user'] ?? [];
                $name = $user['name'] ?? '';
                $ip = $user['ip'] ?? '';
                $method = $user['method'] ?? '';

                $runningIndexData = [];
                $nextIndexData = [];
                $runningIndexSecondKeyData = [];
                $nextIndexSecondData = [];

                if (!empty($e['data']['params']) && is_array($e['data']['params'])) {
                    $dataArr = $e['data']['params'];
                    $firstKey = array_key_first($dataArr);
                    $runningIndexData = $firstKey !== null ? ($dataArr[$firstKey] ?? []) : [];
                    $data = json_encode($runningIndexData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } else {
                    $data = '';
                }

                // Compute changes vs next entry within the same update_id group
                $nextIndex = $i + 1;
                $nextEntry = $groupEntries[$nextIndex] ?? [];
                if (!empty($nextEntry) && !empty($nextEntry['data']['params']) && is_array($nextEntry['data']['params'])) {
                    $nextDataArr = $nextEntry['data']['params'];
                    $firstKeyN = array_key_first($nextDataArr);
                    $nextIndexData = $firstKeyN !== null ? ($nextDataArr[$firstKeyN] ?? []) : [];
                }

                $changed = '';
                if (!empty($runningIndexData) && !empty($nextIndexData)) {
                    $diff = $this->collectChanges($runningIndexData, $nextIndexData);
                    if (!empty($diff)) {
                        $changed = '<pre class="pre-json">' .
                            htmlspecialchars(json_encode($diff, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') .
                            '</pre>';
                    }
                }

                $rowClass = ($changed !== 'No' && $changed !== '') ? ' class="table-warning-1"' : '';
                $changedCell = ($changed === '' || $changed === 'No') ? '<span class="text-muted"></span>' : $changed;

                echo '<tr' . $rowClass . '>';
                echo '<td>' . $rowIndex . '</td>';
                echo '<td>' . htmlspecialchars((string)$groupId, ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($ts, ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td><pre class="pre-json">' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '</pre></td>';
                echo '<td>' . ($changedCell) . '</td>';
                echo '</tr>';
            }
        }

        if (empty($entries)) {
            echo '<tr><td colspan="7" class="text-center">No details found for this route.</td></tr>';
        }
        echo '</tbody></table></div>';
        return ob_get_clean();
    }

    /**
     * Recursively collect all changed fields between two arrays/values.
     * Returns an associative array where keys are dot-notated paths and values are arrays: ['old' => ..., 'new' => ...]
     * Example: ['a.b' => ['old' => 1, 'new' => 2]]
     * @param mixed $old
     * @param mixed $new
     * @param string $path
     * @return array
     */
    private function collectChanges($old, $new, string $path = ''): array
    {
        $changes = [];

        // If both are arrays, walk keys recursively
        if (is_array($old) && is_array($new)) {
            $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
            foreach ($allKeys as $key) {
                $subPath = $path === '' ? (string)$key : $path . '.' . $key;
                $oldExists = array_key_exists($key, $old);
                $newExists = array_key_exists($key, $new);

                if ($oldExists && $newExists) {
                    // Recurse or compare
                    if (is_array($old[$key]) || is_array($new[$key])) {
                        $nested = $this->collectChanges($old[$key], $new[$key], $subPath);
                        $changes = $changes + $nested;
                    } else {
                        if ($old[$key] !== $new[$key]) {
                            // $changes[$subPath] = ['old' => $old[$key], 'new' => $new[$key]];
                            $changes[$subPath] = ['new' => $old[$key], 'old' => $new[$key]];
                        }
                    }
                } elseif ($oldExists && !$newExists) {
                    // Removed key
                    // $changes[$subPath] = ['old' => $old[$key], 'new' => null];
                    $changes[$subPath] = ['new' => $old[$key], 'old' => null];
                } elseif (!$oldExists && $newExists) {
                    // Added key
                    // $changes[$subPath] = ['old' => null, 'new' => $new[$key]];
                    $changes[$subPath] = ['new' => null, 'old' => $new[$key]];
                }
            }
            return $changes;
        }

        // Non-array values: if different, record change at current path (or root)
        if ($old !== $new) {
            // $changes[$path !== '' ? $path : 'value'] = ['old' => $old, 'new' => $new];
            $changes[$path !== '' ? $path : 'value'] = ['new' => $old, 'old' => $new];
        }
        return $changes;
    }
}
