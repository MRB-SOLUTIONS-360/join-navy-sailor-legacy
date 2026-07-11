<?php

Yii::setAlias('@rootDirFilUpload', realpath(dirname(__FILE__).'/../../'));
Yii::setAlias('@rootMediaShow', 'https://www.joinnavysailor.org/');
// Yii::setAlias('@rootMediaShow', '/joinnavyV2/');
Yii::setAlias('@baseUrl', 'https://www.joinnavysailor.org/'); 
//Yii::setAlias('@baseUrl', 'https://joinnavysailor.unlockliveit.com/'); 
Yii::setAlias('@rootMediaShow', 'https://www.joinnavysailor.org/');

 


return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
];
