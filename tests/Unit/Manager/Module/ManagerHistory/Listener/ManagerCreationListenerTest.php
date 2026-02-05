<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\ManagerHistory\Listener;

use App\Entity\Manager;
use App\Entity\ManagerHistory;
use App\Entity\User;
use App\Manager\Module\ManagerHistory\Listener\ManagerCreationListener;
use App\Manager\Module\User\Event\UserRegistered;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\User\UserFinderInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ManagerCreationListener::class)]
#[Group('unit-tests')]
#[AllowMockObjectsWithoutExpectations]
class ManagerCreationListenerTest extends TestCase
{
    private UserFinderInterface&MockObject $finder;
    private CreateEntityInterface&MockObject $historyRepository;
    private LoggerInterface&MockObject $logger;
    private ManagerCreationListener $listener;

    protected function setUp(): void
    {
        error_reporting(E_NOTICE);
        $this->finder = $this->createMock(UserFinderInterface::class);
        $this->historyRepository = $this->createMock(CreateEntityInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new ManagerCreationListener(
            $this->finder,
            $this->historyRepository,
            $this->logger
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->finder, $this->historyRepository, $this->logger, $this->listener);

        parent::tearDown();
    }

    #[Test]
    public function itRegistrationCompletedSucceeds(): void
    {
        $uuid = Uuid::v7();
        $event = new UserRegistered($uuid);
        $user = $this->createMock(User::class);
        $manager = $this->createMock(Manager::class);

        $this->finder->expects($this->once())
            ->method('byUuid')
            ->with($uuid)
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        $this->historyRepository->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (ManagerHistory $history) use ($manager) {
                return $history->getManager() === $manager && $history->getMessage() === 'Manager account created';
            }))
            ->willReturnSelf();

        $this->historyRepository->expects($this->once())
            ->method('flush')
            ->willReturnSelf();

        $this->listener->registrationCompleted($event);
    }

    #[Test]
    public function itRegistrationCompletedLogsErrorIfUserNotFound(): void
    {
        $uuid = Uuid::v7();
        $event = new UserRegistered($uuid);

        $this->finder->expects($this->once())
            ->method('byUuid')
            ->with($uuid)
            ->willReturn(null);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains($uuid->toString()));

        $this->historyRepository->expects($this->never())->method('persist');

        $this->listener->registrationCompleted($event);
    }

    #[Test]
    public function itRegistrationCompletedLogsErrorIfManagerNotFound(): void
    {
        $uuid = Uuid::v7();
        $event = new UserRegistered($uuid);
        $user = $this->createMock(User::class);
        $email = 'test@example.com';

        $this->finder->expects($this->once())
            ->method('byUuid')
            ->with($uuid)
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getManager')
            ->willReturn(null);

        $user->expects($this->once())
            ->method('getEmailAddress')
            ->willReturn($email);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains($email));

        $this->historyRepository->expects($this->never())->method('persist');

        $this->listener->registrationCompleted($event);
    }
}
