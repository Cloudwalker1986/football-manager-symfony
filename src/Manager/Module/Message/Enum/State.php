<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Enum;

enum State: string
{
    case READ = 'read';
    case UNREAD = 'unread';
}
