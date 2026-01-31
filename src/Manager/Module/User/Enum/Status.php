<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Enum;

enum Status: string
{
    case NOT_VERIFIED = 'not_verified';
    case VERIFIED = 'verified';
    case DELETED = 'deleted';
    case NULL = '';

    public function isNull(): bool
    {
        return $this === self::NULL;
    }

    public function isVerified(): bool
    {
        return $this === self::VERIFIED;
    }

    public function isNotVerified(): bool
    {
        return $this === self::NOT_VERIFIED;
    }

    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }
}
