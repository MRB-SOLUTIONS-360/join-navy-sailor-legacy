<?php

namespace bpsys\yii2\aws\s3\interfaces;

use bpsys\yii2\aws\s3\interfaces\commands\Command;
use bpsys\yii2\aws\s3\interfaces\handlers\Handler;

/**
 * Interface HandlerResolver
 *
 * @package bpsys\yii2\aws\s3\interfaces
 */
interface HandlerResolver
{
    /**
     * @param \bpsys\yii2\aws\s3\interfaces\commands\Command $command
     *
     * @return \bpsys\yii2\aws\s3\interfaces\handlers\Handler
     */
    public function resolve(Command $command): Handler;

    /**
     * @param string $commandClass
     * @param mixed  $handler
     */
    public function bindHandler(string $commandClass, $handler);
}
