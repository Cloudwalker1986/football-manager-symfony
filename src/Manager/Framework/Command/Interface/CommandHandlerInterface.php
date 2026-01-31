<?php

namespace App\Manager\Framework\Command\Interface;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AutoconfigureTag('manager.framework.command.handler')]
interface CommandHandlerInterface
{
    public function supports(CommandInterface $command): bool;

    public function handle(CommandInterface $command): void;
}
