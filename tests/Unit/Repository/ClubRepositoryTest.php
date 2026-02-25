<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\Club;
use App\Entity\Team;
use App\Repository\ClubRepository;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClubRepository::class)]
#[Group('unit-tests')]
class ClubRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\Club", got "App\Entity\Team".');

        $registry = $this->createStub(ManagerRegistry::class);
        $repository = new ClubRepository($registry);

        $repository->persist(new Team());
    }
}
