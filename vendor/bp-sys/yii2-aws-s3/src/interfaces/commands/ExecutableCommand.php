<?php

namespace bpsys\yii2\aws\s3\interfaces\commands;

/**
 * Interface ExecutableCommand
 *
 * @package bpsys\yii2\aws\s3\interfaces\commands
 */
interface ExecutableCommand extends Command
{
    /**
     * @return mixed
     */
    public function execute();
}
