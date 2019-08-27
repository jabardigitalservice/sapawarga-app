<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\User;

trait HasSenderName
{
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    protected function getSenderName()
    {
        return $this->user->name;
    }
}
