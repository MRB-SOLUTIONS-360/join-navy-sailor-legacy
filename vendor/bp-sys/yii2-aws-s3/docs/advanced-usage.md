# Advanced usage

```php
/** @var \bpsys\yii2\aws\s3\interfaces\Service $s3 */
$s3 = Yii::$app->get('s3');

/** @var \bpsys\yii2\aws\s3\commands\GetCommand $command */
$command = $s3->create(GetCommand::class);
$command->inBucket('my-another-bucket')->byFilename('filename.ext')->saveAs('/path/to/local/file.ext');

/** @var \Aws\ResultInterface $result */
$result = $s3->execute($command);

// or async
/** @var \GuzzleHttp\Promise\PromiseInterface $promise */
$promise = $s3->execute($command->async());
```

## Custom commands

Commands have two types: plain commands that's handled by the `PlainCommandHandler` and commands with their own handlers.
The plain commands wrap the native AWS S3 commands.

The plain commands must implement the `PlainCommand` interface and the rest must implement the `Command` interface.
If the command doesn't implement the `PlainCommand` interface, it must have its own handler.

Every handler must extend the `Handler` class or implement the `Handler` interface.
Handlers gets the `S3Client` instance into its constructor.

The implementation of the `HasBucket` and `HasAcl` interfaces allows the command builder to set the values
of bucket and acl by default.

To make the plain commands asynchronously, you have to implement the `Asynchronous` interface.
Also, you can use the `Async` trait to implement this interface.

Consider the following command:

```php
<?php

namespace app\components\s3\commands;

use bpsys\yii2\aws\s3\base\commands\traits\Options;
use bpsys\yii2\aws\s3\interfaces\commands\Command;
use bpsys\yii2\aws\s3\interfaces\commands\HasBucket;

class MyCommand implements Command, HasBucket
{
    use Options;

    protected $bucket;

    protected $something;

    public function getBucket()
    {
        return $this->bucket;
    }

    public function inBucket(string $bucket)
    {
        $this->bucket = $bucket;

        return $this;
    }

    public function getSomething()
    {
        return $this->something;
    }

    public function withSomething(string $something)
    {
        $this->something = $something;

        return $this;
    }
}
```

The handler for this command looks like this:

```php
<?php

namespace app\components\s3\handlers;

use app\components\s3\commands\MyCommand;
use bpsys\yii2\aws\s3\base\handlers\Handler;

class MyCommandHandler extends Handler
{
    public function handle(MyCommand $command)
    {
        return $this->s3Client->someAction(
            $command->getBucket(),
            $command->getSomething(),
            $command->getOptions()
        );
    }
}
```

And usage this command:

```php
/** @var \bpsys\yii2\aws\s3\interfaces\Service */
$s3 = Yii::$app->get('s3');

/** @var \app\components\s3\commands\MyCommand $command */
$command = $s3->create(MyCommand::class);
$command->withSomething('some value')->withOption('OptionName', 'value');

/** @var \Aws\ResultInterface $result */
$result = $s3->execute($command);
```

Custom plain command looks like this:

```php
<?php

namespace app\components\s3\commands;

use bpsys\yii2\aws\s3\interfaces\commands\HasBucket;
use bpsys\yii2\aws\s3\interfaces\commands\PlainCommand;

class MyPlainCommand implements PlainCommand, HasBucket
{
    protected $args = [];

    public function getBucket()
    {
        return $this->args['Bucket'] ?? '';
    }

    public function inBucket(string $bucket)
    {
        $this->args['Bucket'] = $bucket;

        return $this;
    }

    public function getSomething()
    {
        return $this->args['something'] ?? '';
    }

    public function withSomething($something)
    {
        $this->args['something'] = $something;

        return $this;
    }

    public function getName(): string
    {
        return 'AwsS3CommandName';
    }

    public function toArgs(): array
    {
        return $this->args;
    }
}
```

Any command can extend the `ExecutableCommand` class or implement the `Executable` interface that will
allow to execute this command immediately: `$command->withSomething('some value')->execute();`.