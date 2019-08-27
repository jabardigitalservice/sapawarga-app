<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use Hashids\Hashids;
use app\models\UserMessage;

trait HasHashesId
{

    protected function getHashesId()
    {
        $hashids = new Hashids(UserMessage::HASHID_SALT_SECRET, UserMessage::HASHID_LENGTH_PAD);

        return $hashids->encode($this->id);
    }
}
