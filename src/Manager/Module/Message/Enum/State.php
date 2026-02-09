<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Enum;

enum State: string
{
    case READ = 'read';
    case UNREAD = 'unread';

    public function isRead(): bool
    {
        return $this === self::READ;
    }

    public function isUnread(): bool
    {
        return $this === self::UNREAD;

    }
}
