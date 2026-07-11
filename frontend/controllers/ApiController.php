<?php

namespace frontend\controllers;

use common\models\CanDesignation;
use common\models\DeSailors;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Sailors;
use common\models\Subjects;
use common\static\DataEncryption;
use common\static\StaticMethod;
use Yii;
use common\static\Constants;
use yii\web\Response;

class ApiController extends \yii\rest\Controller
{
    public $modelClass = 'common\models\Eligibility';

    public $token = 'a2bc0cb09a28beci86sdbbs94adb9b407e1321122023';

    private $allowed_ip = ['127.0.0.1', '::1'];
    private $allowed_domain = ['127.0.0.1', 'localhost', 'http://joinnavysailor.local/'];

    
}
