# Basic usage

## Usage of the command factory and additional params

```php
/** @var \bpsys\yii2\aws\s3\Service $s3 */
$s3 = Yii::$app->get('s3');

/** @var \Aws\ResultInterface $result */
$result = $s3->commands()->get('filename.ext')->saveAs('/path/to/local/file.ext')->execute();

$result = $s3->commands()->put('filename.ext', 'body')->withContentType('text/plain')->execute();

$result = $s3->commands()->delete('filename.ext')->execute();

$result = $s3->commands()->upload('filename.ext', '/path/to/local/file.ext')->withAcl('private')->execute();

$result = $s3->commands()->restore('filename.ext', $days = 7)->execute();

$result = $s3->commands()->list('path/')->execute();

/** @var bool $exist */
$exist = $s3->commands()->exist('filename.ext')->execute();

/** @var string $url */
$url = $s3->commands()->getUrl('filename.ext')->execute();

/** @var string $signedUrl */
$signedUrl = $s3->commands()->getPresignedUrl('filename.ext', '+2 days')->execute();
```

## Short syntax

```php
/** @var \bpsys\yii2\aws\s3\Service $s3 */
$s3 = Yii::$app->get('s3');

/** @var \Aws\ResultInterface $result */
$result = $s3->get('filename.ext');

$result = $s3->put('filename.ext', 'body');

$result = $s3->delete('filename.ext');

$result = $s3->upload('filename.ext', '/path/to/local/file.ext');

$result = $s3->restore('filename.ext', $days = 7);

$result = $s3->list('path/');

/** @var bool $exist */
$exist = $s3->exist('filename.ext');

/** @var string $url */
$url = $s3->getUrl('filename.ext');

/** @var string $signedUrl */
$signedUrl = $s3->getPresignedUrl('filename.ext', '+2 days');

/** @var string $signedUrl */
// Leave second parameter blank to get from default
$signedUrl = $s3->getPresignedUrl('filename.ext');
```

## Asynchronous execution

```php
/** @var \bpsys\yii2\aws\s3\Service $s3 */
$s3 = Yii::$app->get('s3');

/** @var \GuzzleHttp\Promise\PromiseInterface $promise */
$promise = $s3->commands()->get('filename.ext')->async()->execute();

$promise = $s3->commands()->put('filename.ext', 'body')->async()->execute();

$promise = $s3->commands()->delete('filename.ext')->async()->execute();

$promise = $s3->commands()->upload('filename.ext', 'source')->async()->execute();

$promise = $s3->commands()->list('path/')->async()->execute();
```
