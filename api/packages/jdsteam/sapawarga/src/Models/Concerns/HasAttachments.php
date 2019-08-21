<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\Category;
use app\validator\IsArrayValidator;

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

        return array_map(function ($item) {
            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'url'  => $this->bucket->getFileUrl($item['path']),
            ];
        }, $this->attachments);
    }
}
