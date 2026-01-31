<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Command\UserRegistration;

use App\Manager\Module\User\Command\UserRegistration\UserRegisterCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserRegisterCommand::class)]
#[Group('unit-tests')]
class UserRegistrationCommandTest extends TestCase
{
    #[Test]
    public function commandPropertyVerification(): void
    {
        $command = new UserRegisterCommand()->setEmail('test@test.de')
            ->setPassword('abc')
            ->setManagerName('My Name Is');

        self::assertSame('test@test.de', $command->getEmail());
        self::assertSame('abc', $command->getPassword());
        self::assertSame('My Name Is', $command->getManagerName());
    }
}
