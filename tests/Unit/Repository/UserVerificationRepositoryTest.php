<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\Manager;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\UserVerificationRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserVerificationRepository::class)]
#[Group('unit-tests')]
class UserVerificationRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\UserVerification", got "App\Entity\Manager".');
        $repository = new UserVerificationRepository($this->createStub(ManagerRegistry::class));

        $repository->persist(new Manager());
    }
}
