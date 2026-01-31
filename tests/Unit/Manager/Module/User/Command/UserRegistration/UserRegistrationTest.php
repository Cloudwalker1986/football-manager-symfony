<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Command\UserRegistration;

use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\User\Command\UserRegistration\UserRegisterCommand;
use App\Manager\Module\User\Command\UserRegistration\UserRegisterHandler;
use App\Repository\Interface\CreateEntityInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[CoversMethod(CommandHandlerInterface::class, 'supports')]
#[Group('unit-tests')]
class UserRegistrationTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProviderSupport')]
    public function itSupportsCommand( CommandInterface $command, bool $expected): void
    {
        $commandHandler = new UserRegisterHandler(
            $this->createStub(CreateEntityInterface::class),
            $this->createStub(EventDispatcherInterface::class)
        );

        self::assertEquals($expected, $commandHandler->supports($command));
    }

    public static function dataProviderSupport(): array
    {
        return [
            'command is valid' => [
                'command' => new UserRegisterCommand(),
                'expected' => true
            ],
            'command is invalid' => [
                'command' => new class implements CommandInterface{},
                'expected' => false
            ]
        ];
    }
}
