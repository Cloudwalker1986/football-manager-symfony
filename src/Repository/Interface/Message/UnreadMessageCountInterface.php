<?php

declare(strict_types=1);

namespace App\Repository\Interface\Message;

use App\Entity\Manager;

interface UnreadMessageCountInterface
{
    public function getUnreadMessageCount(Manager $manager): int;
}
