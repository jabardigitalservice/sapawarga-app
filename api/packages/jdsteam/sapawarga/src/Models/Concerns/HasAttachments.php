<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\validator\IsArrayValidator;
use Yii;
use yii2tech\filestorage\BucketInterface;

trait HasAttachments
{
    protected $bucket = 'imageFiles';

    protected function rulesAttachments()
    {
        return [
            ['attachments', 'default'],
            ['attachments', IsArrayValidator::class],
        ];
    }

    protected function getAttachmentsField()
    {
        if ($this->attachments === null) {
            return null;
        }

        /**
         * @var BucketInterface $bucket
         */
        $bucket = Yii::$app->fileStorage->getBucket($this->bucket);

        return array_map(function ($item) use ($bucket) {
            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'url'  => $bucket->getFileUrl($item['path']),
            ];
        }, $this->attachments);
    }
}
