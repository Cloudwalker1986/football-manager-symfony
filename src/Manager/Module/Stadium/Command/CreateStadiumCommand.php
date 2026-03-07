<?php

declare(strict_types=1);

namespace App\Manager\Module\Stadium\Command;

use App\Manager\Framework\Command\Interface\CommandInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateStadiumCommand implements CommandInterface
{
    public function __construct(public Uuid $clubUuid)
    {
    }
}
