<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\Club;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\TeamRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamRepository::class)]
#[Group('unit-tests')]
class TeamRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\Team", got "App\Entity\Club".');

        $registry = $this->createStub(ManagerRegistry::class);
        $repository = new TeamRepository($registry);

        $repository->persist(new Club());
    }
}
