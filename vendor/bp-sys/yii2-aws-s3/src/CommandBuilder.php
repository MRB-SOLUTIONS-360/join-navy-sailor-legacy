<?php

namespace bpsys\yii2\aws\s3;

use bpsys\yii2\aws\s3\interfaces;

/**
 * Class CommandBuilder
 *
 * @package bpsys\yii2\aws\s3
 */
class CommandBuilder implements interfaces\CommandBuilder
{
    /** @var string default bucket name */
    protected $bucket;

    /** @var string default acl */
    protected $acl;

    /** @var int|string|\DateTime default expiration */
    protected $expiration;

    /** @var interfaces\Bus */
    protected $bus;

    /**
     * CommandBuilder constructor.
     *
     * @param \bpsys\yii2\aws\s3\interfaces\Bus $bus
     * @param string $bucket
     * @param string $acl
     * @param int|string|\DateTime $expiration
     */
    public function __construct(interfaces\Bus $bus, string $bucket = '', string $acl = '', $expiration = '')
    {
        $this->bus = $bus;
        $this->bucket = $bucket;
        $this->acl = $acl;
        $this->expiration = $expiration;
    }

    /**
     * @param string $className
     *
     * @return \bpsys\yii2\aws\s3\interfaces\commands\Command
     * @throws \yii\base\InvalidConfigException
     */
    public function build(string $className): interfaces\commands\Command
    {
        $params = is_subclass_of($className, interfaces\commands\ExecutableCommand::class) ? [$this->bus] : [];

        /** @var interfaces\commands\Command $command */
        $command = \Yii::createObject($className, $params);

        $this->prepareCommand($command);

        return $command;
    }

    /**
     * @param \bpsys\yii2\aws\s3\interfaces\commands\Command $command
     */
    protected function prepareCommand(interfaces\commands\Command $command)
    {
        if ($command instanceof interfaces\commands\HasBucket) {
            $command->inBucket($this->bucket);
        }

        if ($command instanceof interfaces\commands\HasAcl) {
            $command->withAcl($this->acl);
        }

        if ($command instanceof interfaces\commands\HasExpiration) {
            $command->withExpiration($this->expiration);
        }
    }
}
