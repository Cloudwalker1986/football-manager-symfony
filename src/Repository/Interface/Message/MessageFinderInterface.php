<?php

namespace App\Repository\Interface\Message;

use App\Entity\Message;
use Symfony\Component\Uid\Uuid;

interface MessageFinderInterface
{
    public function byUuid(Uuid $uuid): ?Message;

    /**
     * @return Message[]
     */
    public function findOlderThan(\DateTimeImmutable $date): array;
}
