<?php

namespace bpsys\yii2\aws\s3\interfaces\commands;

/**
 * Interface HasExpiration
 *
 * @package bpsys\yii2\aws\s3\interfaces\commands
 */
interface HasExpiration
{
    /**
     * @param int|string|\DateTime $expiration
     */
    public function withExpiration($expiration);
}
