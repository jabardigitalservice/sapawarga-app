<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use Yii;

trait HasActiveStatus
{
    protected function getStatusLabel()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                $statusLabel = Yii::t('app', 'status.active');
                break;
            case self::STATUS_DISABLED:
                $statusLabel = Yii::t('app', 'status.inactive');
                break;
            case self::STATUS_DELETED:
                $statusLabel = Yii::t('app', 'status.deleted');
                break;
            default:
                $statusLabel = '';
                break;
        }

        return $statusLabel;
    }
}
