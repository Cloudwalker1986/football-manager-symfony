<?php

declare(strict_types=1);

namespace App\UnitTests\Repository;

use App\Entity\ManagerHistory;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\ManagerHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagerHistoryRepository::class)]
#[Group('unit-tests')]
#[AllowMockObjectsWithoutExpectations]
class ManagerHistoryRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private ManagerRegistry&MockObject $registry;
    private ManagerHistoryRepository $repository;

    protected function setUp(): void
    {
        error_reporting(E_NOTICE);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManagerForClass')->willReturn($this->entityManager);

        $classMetadata = new \Doctrine\ORM\Mapping\ClassMetadata(ManagerHistory::class);
        $this->entityManager->method('getClassMetadata')->willReturn($classMetadata);

        $this->repository = new ManagerHistoryRepository($this->registry);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->entityManager, $this->registry, $this->repository);

        parent::tearDown();
    }

    #[Test]
    public function itPersistsSucceedsWithCorrectEntityType(): void
    {
        $entity = new ManagerHistory();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($entity);

        $result = $this->repository->persist($entity);

        $this->assertSame($this->repository, $result);
    }

    #[Test]
    public function itPersistsThrowsExceptionWithIncorrectEntityType(): void
    {
        $entity = new \stdClass();

        $this->expectException(InvalidEntityArgumentTypeException::class);

        $this->repository->persist($entity);
    }

    #[Test]
    public function itFlushesSucceeds(): void
    {
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->repository->flush();

        $this->assertSame($this->repository, $result);
    }
}
