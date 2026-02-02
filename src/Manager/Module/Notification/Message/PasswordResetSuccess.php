<?php

declare(strict_types=1);

namespace App\Manager\Module\Notification\Message;

use Symfony\Component\Uid\Uuid;

readonly class PasswordResetSuccess
{
    public function __construct(
        public Uuid $userUuid
    ) {
    }
}
