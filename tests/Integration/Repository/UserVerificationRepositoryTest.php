<?php

declare(strict_types=1);

namespace App\IntegrationTests\Repository;

use App\Entity\User;
use App\Entity\UserVerification;
use App\Manager\Module\User\Enum\Status;
use App\Repository\UserVerificationRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Uid\Uuid;

#[Group('integration-tests')]
class UserVerificationRepositoryTest extends AbstractRepositoryTestCase
{
    private ?UserVerificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(UserVerificationRepository::class);
    }

    #[Test]
    public function persistAndFlush(): void
    {
        $user = new User();
        $user->setEmailAddress('verification@example.com');
        $user->setPassword('password');
        $user->setStatus(Status::VERIFIED);
        $this->entityManager->persist($user);

        $verification = new UserVerification();
        $verification->setExpiresAt(new \DateTimeImmutable('+1 hour'));
        $verification->setUser($user);

        $this->repository->persist($verification);
        $this->repository->flush();

        $this->assertNotNull($verification->getUuid());

        $found = $this->repository->find($verification->getId());
        $this->assertSame($verification, $found);
    }

    #[Test]
    public function findByUuid(): void
    {
        $user = new User();
        $user->setEmailAddress('verification-uuid@example.com');
        $user->setPassword('password');
        $user->setStatus(Status::VERIFIED);
        $this->entityManager->persist($user);

        $verification = new UserVerification();
        $verification->setExpiresAt(new \DateTimeImmutable('+1 hour'));
        $verification->setUser($user);

        $this->repository->persist($verification);
        $this->repository->flush();

        $uuid = $verification->getUuid();
        $this->assertInstanceOf(Uuid::class, $uuid);

        $found = $this->repository->findOneBy(['uuid' => $uuid->toString()]);
        $this->assertSame($verification, $found);
    }
}
