<?php

namespace Jdsteam\Sapawarga\Models\Contracts;

interface ActiveStatus
{
    public const STATUS_DELETED = -1;
    public const STATUS_DISABLED = 0;
    public const STATUS_ACTIVE = 10;
}
