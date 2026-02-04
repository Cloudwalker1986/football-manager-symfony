<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Framework\Command\Interface;

use App\Manager\Framework\Command\CommandBus;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CommandBus::class)]
#[CoversClassesThatImplementInterface(CommandHandlerInterface::class)]
#[Group('unit-tests')]
class CommandBusTest extends KernelTestCase
{
    #[Test]
    public function commandCounter(): void
    {
        $commandBus = self::getContainer()->get(CommandBus::class);

        $classReflection = new \ReflectionClass($commandBus);
        $property = $classReflection->getProperty('handlers');

        self::assertCount(
            2,
            $property->getValue($commandBus),
            'Handlers does not match the expected count'
        );
    }
}
