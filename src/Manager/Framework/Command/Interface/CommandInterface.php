<?php

declare(strict_types=1);

namespace App\Manager\Framework\Command\Interface;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('manager.framework.command')]
interface CommandInterface
{

}
