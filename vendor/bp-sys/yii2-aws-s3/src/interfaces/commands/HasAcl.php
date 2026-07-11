<?php

namespace bpsys\yii2\aws\s3\interfaces\commands;

/**
 * Interface HasAcl
 *
 * @package bpsys\yii2\aws\s3\interfaces\commands
 */
interface HasAcl
{
    /**
     * @param string $acl
     */
    public function withAcl(string $acl);
}
