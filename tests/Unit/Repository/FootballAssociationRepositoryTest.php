<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\FootballAssociation;
use App\Entity\League;
use App\Repository\FootballAssociationRepository;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FootballAssociationRepository::class)]
#[Group('unit-tests')]
class FootballAssociationRepositoryTest extends TestCase
{
    #[Test]
    public function invalidEntityType(): void
    {
        self::expectException(InvalidEntityArgumentTypeException::class);
        self::expectExceptionMessage('Invalid argument type for $entity. Expected "App\Entity\FootballAssociation", got "App\Entity\League".');

        $registry = $this->createStub(ManagerRegistry::class);
        $repository = new FootballAssociationRepository($registry);

        $repository->persist(new League());
    }
}
