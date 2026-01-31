<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Event;

use Symfony\Component\Uid\Uuid;

readonly class UserRegistered
{
    public function __construct(public Uuid $userUuid)
    {
    }
}
