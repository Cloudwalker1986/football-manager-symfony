<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\Manager;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserRepository::class)]
#[Group('unit-tests')]
class UserRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\User", got "App\Entity\Manager".');
        $repository = new UserRepository($this->createStub(ManagerRegistry::class));

        $repository->persist(new Manager());
    }
}
