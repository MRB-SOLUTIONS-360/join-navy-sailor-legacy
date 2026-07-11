<?php

namespace common\components;

use Yii;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use yii\base\Component;
use yii\base\ErrorException;

class R2Storage extends Component
{
    public $accessKey;
    public $secretKey;
    public $bucket;
    public $region;
    public $endpoint;
    public $fileUrl = '';
    public $verifySsl;

    private $_client;

    // Initialize the S3 client (Cloudflare R2)
    public function init()
    {
        parent::init();

        $this->_client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpoint,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ],
            'http' => [
                'verify' => $this->verifySsl,
            ],
        ]);
    }

    /**
     * Upload file to Cloudflare R2 storage.
     * 
     * @param string $fileName The file name to be saved in the bucket.
     * @param string $path The local path to the file.
     * @return string|false The file URL if uploaded successfully, false otherwise.
     * @throws ErrorException
     */
    public function uploadFile($fileName, $path)
    {
        try {
            $result = $this->_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fileName,
                'SourceFile' => $path, // Temporary file on server
            ]);

            if ($result->get('ObjectURL')) {
                // return $result->get('ObjectURL');
                return true;
            }
        } catch (AwsException $e) {
            // Yii::error('Error uploading file: ' . $e->getMessage(), __METHOD__);
            // throw new ErrorException($e->getMessage());
            return false;
        }

        return false;
    }

    public function fileExists($fileName)
    {
        try {
            // Call headObject to check if the file exists in the bucket
            $result = $this->_client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $fileName,
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function deleteFile($fileName)
    {
        try {
            // Check if the file exists in the R2 bucket
            if ($this->fileExists($fileName)) {
                // File exists, so proceed to delete it
                $result = $this->_client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key' => $fileName,
                ]);
                return true;
            } else {
                return false;
            }
        } catch (AwsException $e) {
            return false;
        }
    }



    private $logFile = 'logs.ndjson';
    // Candidate Log
    public function upsertCandidateLog(array $model, $logFile = null)
    {
        $requestUser = Yii::$app->user->identity;
        $isoDate = date('c');
        $logEntry = [
            'id' => $model['id'],
            'data' => [
                [
                    'candidate' => $model ?: [],
                    'user' => [
                        'id' => $requestUser->id,
                        'name' => $requestUser->username,
                        'date_time' =>  $isoDate,
                    ],
                ]
            ],
        ];

        $currentLogFile = $logFile ?? $this->logFile;
        $contents = $this->getLogFileContents($currentLogFile);
        $lines = explode("\n", trim($contents));

        $newLogLines = '';
        $exists = false;
        foreach ($lines as &$line) {
            if ($line) {
                $existingEntry = json_decode($line, true);
                if ($existingEntry['id'] == $logEntry['id']) {
                    $existingEntry['data'] = array_merge($existingEntry['data'], $logEntry['data']); // Merge new data
                    $existingEntry['timestamp'] = $isoDate;
                    $line = json_encode($existingEntry);
                    $exists = true;
                }
                $newLogLines .= $line . "\n";
            }
        }
        // If the log entry doesn't exist, add a new one
        if (!$exists) {
            $logEntry['timestamp'] = $isoDate;
            $newLogLines .= json_encode($logEntry) . "\n";
        }
        try {
            $this->_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => 'logs/' . $currentLogFile,
                'Body' => $newLogLines,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // log  
    public function getLogFileContents($logFile = null)
    {
        $currentLogFile = $logFile ?? $this->logFile;      
        try {
            $result = $this->_client->getObject([
                'Bucket' => $this->bucket,
                'Key' => 'logs/' . $currentLogFile,
            ]); 
            return (string) $result['Body'];
        } catch (\Exception $e) {
            return '';
        }
    }

    // action log
    public function actionLog(array $logData, $logFile = null)
    {

        $currentLogFile = $logFile ?? $this->logFile;
        $contents = $this->getLogFileContents($currentLogFile);
        $lines = explode("\n", trim($contents));
        
        $newLogLines = '';
        $exists = false;
        $isoDate = date('c');
        foreach ($lines as &$line) {
            if ($line) {
                $existingEntry = json_decode($line, true);           
                if ($existingEntry[0]['route'] == $logData['route']) {
                    $logData['timestamp'] =$isoDate;               
                    array_unshift($existingEntry, $logData);          
                    $line = json_encode($existingEntry);
                    $exists = true;
                }
                $newLogLines .= $line . "\n";
            }
        }

        // If the log entry doesn't exist, add a new one
        if (!$exists) {
            $logData['timestamp'] = $isoDate;
            $newLogLines .= json_encode([$logData]) . "\n";
        }
        try {
            $this->_client->putObject([
                'Bucket' => $this->bucket,
                'Key' => 'logs/' . $currentLogFile,
                'Body' => $newLogLines,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }        
    }
}
