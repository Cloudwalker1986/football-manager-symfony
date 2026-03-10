<?php

declare(strict_types=1);

namespace App\Manager\Module\Club\Event;

use Symfony\Component\Uid\Uuid;

readonly class ClubCreated
{
    public function __construct(public Uuid $clubUuid)
    {
    }
}
