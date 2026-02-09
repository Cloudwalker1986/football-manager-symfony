<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Message;
use App\Manager\Module\Message\Enum\State;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class MessageViewTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanViewMessageAndAutomaticallyMarkAsRead(): void
    {
        $user = $this->createUserWithManager('manager@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Test message content");
        $message->setSubject("Test subject");
        $message->setManager($manager);
        $message->setState(State::UNREAD);
        $this->messageRepository->persist($message)->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', sprintf('/de/messages/%s/view', $message->getUuid()));

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($message->getUuid(), $response['uuid']);
        self::assertEquals('Test subject', $response['subject']);
        self::assertEquals('Test message content', $response['message']);
        self::assertEquals('read', $response['state']);

        // Verify state was updated in database
        $this->messageRepository->getEntityManager()->refresh($message);
        self::assertEquals(State::READ, $message->getState());
    }

    #[Test]
    public function itReturnsMessageWithSenderInfo(): void
    {
        $user1 = $this->createUserWithManager('sender@example.com', 'password', 'Sender Manager');
        $sender = $user1->getManager();

        $user2 = $this->createUserWithManager('receiver@example.com', 'password', 'Receiver Manager');
        $receiver = $user2->getManager();

        $message = new Message();
        $message->setMessage("Message with sender");
        $message->setSubject("Subject with sender");
        $message->setManager($receiver);
        $message->setSender($sender);
        $message->setState(State::UNREAD);
        $this->messageRepository->persist($message)->flush();

        $this->client->loginUser($user2);
        $this->client->request('GET', sprintf('/de/messages/%s/view', $message->getUuid()));

        self::assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals('Sender Manager', $response['sender']);
    }

    #[Test]
    public function itIsIdempotentForAlreadyReadMessages(): void
    {
        $user = $this->createUserWithManager('manager2@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Already read");
        $message->setSubject("Already read subject");
        $message->setManager($manager);
        $message->setState(State::READ);
        $this->messageRepository->persist($message)->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', sprintf('/de/messages/%s/view', $message->getUuid()));

        self::assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals('read', $response['state']);

        // Verify state is still READ
        $this->messageRepository->getEntityManager()->refresh($message);
        self::assertEquals(State::READ, $message->getState());
    }

    #[Test]
    public function itReturnsNotFoundForInvalidUuid(): void
    {
        $user = $this->createUserWithManager('manager3@example.com', 'password');

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/invalid-uuid-12345/view');

        self::assertResponseStatusCodeSame(404);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $response);
    }

    #[Test]
    public function itDeniesAccessToOtherManagersMessages(): void
    {
        $user1 = $this->createUserWithManager('manager4@example.com', 'password');
        $manager1 = $user1->getManager();

        $user2 = $this->createUserWithManager('manager5@example.com', 'password');
        $manager2 = $user2->getManager();

        $message = new Message();
        $message->setMessage("Manager 2 message");
        $message->setSubject("Manager 2 subject");
        $message->setManager($manager2);
        $this->messageRepository->persist($message)->flush();

        // Login as manager1 and try to view manager2's message
        $this->client->loginUser($user1);
        $this->client->request('GET', sprintf('/de/messages/%s/view', $message->getUuid()));

        self::assertResponseStatusCodeSame(403);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $response);

        // Verify state was NOT changed
        $this->messageRepository->getEntityManager()->refresh($message);
        self::assertEquals(State::UNREAD, $message->getState());
    }

    #[Test]
    public function itRedirectsToLoginWhenNotAuthenticated(): void
    {
        $user = $this->createUserWithManager('manager6@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Test message");
        $message->setSubject("Test subject");
        $message->setManager($manager);
        $this->messageRepository->persist($message)->flush();

        // Do not login
        $this->client->request('GET', sprintf('/de/messages/%s/view', $message->getUuid()));

        self::assertResponseRedirects('/de/login');
    }

    #[Test]
    public function itDeniesAccessWhenUserHasNoManager(): void
    {
        // Create user without manager
        $user = new \App\Entity\User();
        $user->setEmailAddress('no-manager@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setStatus(\App\Manager\Module\User\Enum\Status::VERIFIED);
        $user->setLocale('de');
        $this->userRepository->persist($user)->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/some-uuid/view');

        self::assertResponseStatusCodeSame(403);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $response);
    }
}
