<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Command;

use App\Manager\Framework\Command\Interface\CommandInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateClubMessageCommand implements CommandInterface
{
    public function __construct(
        public Uuid $clubUuid,
    ) {

    }
}
