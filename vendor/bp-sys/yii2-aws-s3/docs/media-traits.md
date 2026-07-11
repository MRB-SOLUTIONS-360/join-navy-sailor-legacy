# Using media Traits

## Attach Trait to Model/ActiveRecord

Attach the Trait to the Model/ActiveRecord with some media attribute that will be saved in S3:

```php
/**
 * @property string $image
 */
class Client extends \yii\db\ActiveRecord
{
    use \common\traits\S3MediaTrait;

    public function rules()
    {
        return [
            ['image', 'string'], // Stores the filename
        ];
    }

    protected function attributePaths()
    {
        return [
            'image' => 'images/'
        ];
    }

    // ...
}
```

Override the `attributePaths()` method to change the base path where the files will be saved on AWS S3.

* You can map a different path to each file attribute of your Model/ActiveRecord.

## Using the media Trait

```php
$image = \yii\web\UploadedFile::getInstance( $formModel, 'my_file_attribute' );
// Save image as my_image.png on S3 at //my_bucket/images/ path
// $model->image will hold "my_image.png" after this call finish with success
$model->saveUploadedFile( $image, 'image', 'my_image.png' );

// Get the URL to the image on S3
$model->getFileUrl( 'image' );
// Get the presigned URL to the image on S3
// The default duration is "+1 day"
$model->getFilePresignedUrl( 'image' );

// Remove the file with named saved on the image attribute
// Continuing the example, here "//my_bucket/images/my_image.png" will be deleted from S3
$model->removeFile( 'image' );

// Save my_image.* to S3 on //my_bucket/images/ path
// The extension of the file will be determined by the submitted file type
// This allows multiple file types upload (png,jpg,gif,...)
$model->saveUploadedFile( $image, 'image', 'my_image', true );
```

### Overrinding methods

#### getS3Component

The S3MediaTrait depends on this component to be configured. The default configuration is to use this component on index `'s3'`, but you may use another value. For this cases, override the `getS3Component()` method:

```php
public function getS3Component()
{
    return Yii::$app->get('my_s3_component');
}
```

#### attributePaths

The main method to override is `attributePaths()`, which defines a path in S3 for each attribute of yout model. Allowing you to save each attribute in a different S3 folder.

Here an example:

```php
protected function attributePaths()
{
    return [
        'logo' => 'logos/',
        'badge' => 'images/badges/'
    ];
}
```

#### getPresignedUrlDuration

The default pressigned URL duration is set to "+1day", override this method and use your own expiration.

```php
protected function getPresignedUrlDuration()
{
    return '+2 hours';
}
```

The value should be a valid PHP datetime operation. Read [PHP documentation](https://www.php.net/manual/en/datetime.formats.php) for details

#### isSuccessResponseStatus

The `isSuccessResponseStatus()` method validate the AWS response for status codes between 200 and 204. If needed, you can override this validation:

```php
protected function isSuccessResponseStatus($response)
{
    // Response is always valid
    return true;
}
```
