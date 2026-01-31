<?php

declare(strict_types=1);

namespace App\IntegrationTests\Repository;

use App\Entity\Manager;
use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\ManagerRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class ManagerRepositoryTest extends AbstractRepositoryTestCase
{
    private ?ManagerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(ManagerRepository::class);
    }

    #[Test]
    public function isManagerNameUnique(): void
    {
        $name = 'John Doe';

        // Initially unique
        $this->assertTrue($this->repository->isManagerNameUnique($name));

        $user = new User();
        $user->setEmailAddress('manager@example.com');
        $user->setPassword('password');
        $user->setStatus(Status::VERIFIED);
        $this->entityManager->persist($user);

        $manager = new Manager();
        $manager->setName($name);
        $manager->setUser($user);

        $this->repository->persist($manager);
        $this->repository->flush();

        // No longer unique
        $this->assertFalse($this->repository->isManagerNameUnique($name));
    }
}
