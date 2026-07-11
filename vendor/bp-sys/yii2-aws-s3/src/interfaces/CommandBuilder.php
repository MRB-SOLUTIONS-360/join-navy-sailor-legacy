<?php

namespace bpsys\yii2\aws\s3\interfaces;

use bpsys\yii2\aws\s3\interfaces\commands\Command;

/**
 * Interface CommandBuilder
 *
 * @package bpsys\yii2\aws\s3\interfaces
 */
interface CommandBuilder
{
    /**
     * @param string $commandClass
     *
     * @return \bpsys\yii2\aws\s3\interfaces\commands\Command
     */
    public function build(string $commandClass): Command;
}
