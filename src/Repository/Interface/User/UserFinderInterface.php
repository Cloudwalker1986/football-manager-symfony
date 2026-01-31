<?php

declare(strict_types=1);

namespace App\Repository\Interface\User;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

interface UserFinderInterface
{
    public function byUuid(Uuid $uuid): ?User;
}
