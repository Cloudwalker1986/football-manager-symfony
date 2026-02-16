<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Command\DeleteMessage;

use App\Manager\Framework\Command\Interface\CommandInterface;

readonly class DeleteMessageCommand implements CommandInterface
{
    public function __construct(
        private string $uuid
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
