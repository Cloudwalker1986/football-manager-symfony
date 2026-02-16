<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Message;
use App\Manager\Module\Message\Enum\State;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class MessageDeleteTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanDeleteMessage(): void
    {
        $user = $this->createUserWithManager('manager@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Test message content");
        $message->setSubject("Test subject");
        $message->setManager($manager);
        $message->setState(State::UNREAD);
        $this->messageRepository->persist($message)->flush();

        $uuid = $message->getUuid();

        $this->client->loginUser($user);

        $this->client->request(
            'POST',
            sprintf('/de/messages/%s/delete', $uuid)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($response['success']);

        // Verify it's gone from database
        $this->messageRepository->getEntityManager()->clear();
        $deletedMessage = $this->messageRepository->findOneBy(['uuid' => $uuid]);
        self::assertNull($deletedMessage);
    }

    #[Test]
    public function itDeniesAccessToDeleteOtherManagersMessages(): void
    {
        $user1 = $this->createUserWithManager('manager3@example.com', 'password');
        $user2 = $this->createUserWithManager('manager4@example.com', 'password');
        $manager2 = $user2->getManager();

        $message = new Message();
        $message->setManager($manager2);
        $message->setMessage("Manager 2 message");
        $this->messageRepository->persist($message)->flush();

        $this->client->loginUser($user1);

        $this->client->request(
            'POST',
            sprintf('/de/messages/%s/delete', $message->getUuid())
        );

        self::assertResponseStatusCodeSame(403);

        // Verify message still exists
        $this->messageRepository->getEntityManager()->clear();
        $stillThere = $this->messageRepository->findOneBy(['uuid' => $message->getUuid()]);
        self::assertNotNull($stillThere);
    }
}
