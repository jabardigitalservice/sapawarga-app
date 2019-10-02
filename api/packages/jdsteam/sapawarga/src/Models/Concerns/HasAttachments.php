<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\validator\IsArrayValidator;
use Yii;

trait HasAttachments
{
    protected function rulesAttachments()
    {
        return [
            ['attachments', 'default'],
            ['attachments', IsArrayValidator::class],
        ];
    }

    public function setDefaultBucket($bucket = null)
    {
        //
    }

    public function setBucket($bucket)
    {
        //
    }

    protected function getAttachmentsField()
    {
        if ($this->attachments === null) {
            return null;
        }

        return array_map(function ($item) {
            $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'url'  => "{$publicBaseUrl}/{$item['path']}",
            ];
        }, $this->attachments);
    }
}
