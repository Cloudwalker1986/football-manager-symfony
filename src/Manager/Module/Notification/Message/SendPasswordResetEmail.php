<?php

declare(strict_types=1);

namespace App\Manager\Module\Notification\Message;

use Symfony\Component\Uid\Uuid;

readonly class SendPasswordResetEmail
{
    public function __construct(
        public Uuid $userUuid,
        public string $selector,
        public string $verifier,
        public int $expiresAtInMinutes
    ) {
    }
}
