<?php

declare(strict_types=1);

namespace App\IntegrationTests\Repository;

use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Uid\Uuid;

#[Group('integration-tests')]
class UserRepositoryTest extends AbstractRepositoryTestCase
{
    private ?UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;
        parent::tearDown();
    }

    #[Test]
    public function itGivenEmailAddressIsUnique(): void
    {
        $email = 'test@example.com';

        // Initially unique
        self::assertTrue($this->repository->isEmailAddressUnique($email));

        $user = new User();
        $user->setEmailAddress($email);
        $user->setPassword('password');
        $user->setStatus(Status::VERIFIED);

        $this->repository->persist($user);
        $this->repository->flush();

        // No longer unique
        self::assertFalse($this->repository->isEmailAddressUnique($email));
    }

    #[Test]
    public function itFindsByExistingUuid(): void
    {
        $user = new User();
        $user->setEmailAddress('uuid-test@example.com');
        $user->setPassword('password');
        $user->setStatus(Status::VERIFIED);

        $this->repository->persist($user);
        $this->repository->flush();

        $uuid = $user->getUuid();
        self::assertInstanceOf(Uuid::class, $uuid);

        $foundUser = $this->repository->byUuid($uuid);
        self::assertSame($user, $foundUser);
    }
}
