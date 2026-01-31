<?php

declare(strict_types=1);

namespace App\Manager\Framework\Command;

use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class CommandBus
{
    /**
     * @param CommandHandlerInterface[] $handlers
     */
    public function __construct(
        #[AutowireIterator('manager.framework.command.handler')]
        private iterable $handlers
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($command)) {
                $handler->handle($command);
                return;
            }
        }
    }
}
