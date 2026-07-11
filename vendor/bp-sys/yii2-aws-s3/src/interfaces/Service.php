<?php

namespace bpsys\yii2\aws\s3\interfaces;

use bpsys\yii2\aws\s3\interfaces\commands\Command;

/**
 * Interface Service
 *
 * @package bpsys\yii2\aws\s3\interfaces
 */
interface Service
{
    /**
     * @param \bpsys\yii2\aws\s3\interfaces\commands\Command $command
     *
     * @return mixed
     */
    public function execute(Command $command);

    /**
     * @param string $commandClass
     *
     * @return \bpsys\yii2\aws\s3\interfaces\commands\Command
     */
    public function create(string $commandClass): Command;
}
