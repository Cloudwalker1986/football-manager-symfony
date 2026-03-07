<?php

declare(strict_types=1);

namespace App\Manager\Module\Stadium\Command;

use App\Manager\Framework\Command\Interface\CommandInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateStadiumEnvironmentCommand implements CommandInterface
{
    public function __construct(public Uuid $clubUuid)
    {
    }
}
