<?php

namespace bpsys\yii2\aws\s3\interfaces;

use bpsys\yii2\aws\s3\interfaces\commands\Command;

/**
 * Interface Bus
 *
 * @package bpsys\yii2\aws\s3\interfaces
 */
interface Bus
{
    /**
     * @param \bpsys\yii2\aws\s3\interfaces\commands\Command $command
     *
     * @return mixed
     */
    public function execute(Command $command);
}
