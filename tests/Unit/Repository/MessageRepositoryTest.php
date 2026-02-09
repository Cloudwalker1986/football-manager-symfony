<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\User;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\MessageRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageRepository::class)]
#[Group('unit-tests')]
class MessageRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\Message", got "App\Entity\User".');
        $repository = new MessageRepository($this->createStub(ManagerRegistry::class));

        $repository->persist(new User());
    }
}
