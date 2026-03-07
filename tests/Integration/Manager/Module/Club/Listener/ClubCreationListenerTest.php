<?php

declare(strict_types=1);

namespace App\IntegrationTests\Manager\Module\Club\Listener;

use App\Entity\Club;
use App\Entity\Manager;
use App\Entity\User;
use App\Entity\Stadium;
use App\Entity\StadiumEnvironment;
use App\IntegrationTests\Repository\AbstractRepositoryTestCase;
use App\Manager\Module\Club\Event\ClubCreated;
use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('integration-tests')]
class ClubCreationListenerTest extends AbstractRepositoryTestCase
{
    private ?EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
    }

    #[Test]
    public function itCreatesStadiumAndEnvironmentWhenClubIsCreated(): void
    {
        // 1. Arrange: Use a User and Manager from fixtures
        // user_21 from User.yaml has email manager-21@example.com
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailAddress' => 'manager-21@example.com']);
        self::assertNotNull($user, 'User manager-21@example.com should exist in fixtures');
        $manager = $user->getManager();
        self::assertNotNull($manager, 'Manager for user_2 should exist in fixtures');

        $club = new Club();
        $club->setName('Test Club');
        $club->setShortName('TC');
        $club->setBudget('1000000');
        $club->setManager($manager);

        $this->entityManager->persist($club);
        $this->entityManager->flush();

        $clubUuid = $club->getUuid();
        self::assertNotNull($clubUuid);

        // Assert before state
        self::assertNull($club->getStadium());
        self::assertNull($club->getStadiumEnvironment());

        // 2. Act: Dispatch the event
        $this->eventDispatcher->dispatch(new ClubCreated($clubUuid));

        // 3. Assert: Check after state
        // Refresh club from DB
        $this->entityManager->clear();
        $refreshedClub = $this->entityManager->getRepository(Club::class)->findOneBy(['uuid' => $clubUuid->toString()]);

        self::assertNotNull($refreshedClub);
        self::assertNotNull($refreshedClub->getStadium());
        self::assertNotNull($refreshedClub->getStadiumEnvironment());
        self::assertSame('Test Club Stadium', $refreshedClub->getStadium()->getName());
        self::assertCount(4, $refreshedClub->getStadium()->getBlocks());

        // 4. Test idempotency: Dispatch again
        $stadiumId = $refreshedClub->getStadium()->getId();
        $envId = $refreshedClub->getStadiumEnvironment()->getId();

        $this->eventDispatcher->dispatch(new ClubCreated($clubUuid));

        $this->entityManager->clear();
        $finalClub = $this->entityManager->getRepository(Club::class)->findOneBy(['uuid' => $clubUuid->toString()]);

        self::assertSame($stadiumId, $finalClub->getStadium()->getId());
        self::assertSame($envId, $finalClub->getStadiumEnvironment()->getId());
    }
}
