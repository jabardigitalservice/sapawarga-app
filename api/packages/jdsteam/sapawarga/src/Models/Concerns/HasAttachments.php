<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\validator\IsArrayValidator;
use Yii;
use yii2tech\filestorage\BucketInterface;

trait HasAttachments
{
    /**
     * @var BucketInterface
     */
    protected $bucket;

    protected function rulesAttachments()
    {
        return [
            ['attachments', 'default'],
            ['attachments', IsArrayValidator::class],
        ];
    }

    public function setDefaultBucket($bucket = null)
    {
        /**
         * @var BucketInterface $bucket
         */
        if ($bucket === null && isset(Yii::$app->fileStorage)) {
            $bucket = Yii::$app->fileStorage->getBucket('imageFiles');
        }

        $this->setBucket($bucket);
    }

    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    protected function getAttachmentsField()
    {
        if ($this->attachments === null) {
            return null;
        }

        return array_map(function ($item) {
            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'url'  => $this->bucket->getFileUrl($item['path']),
            ];
        }, $this->attachments);
    }
}
