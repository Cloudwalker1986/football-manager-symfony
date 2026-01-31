<?php

declare(strict_types=1);

namespace App\Repository\Interface\Manager;

interface UniqueManagerNameInterface
{
    public function isManagerNameUnique(string $name): bool;
}
