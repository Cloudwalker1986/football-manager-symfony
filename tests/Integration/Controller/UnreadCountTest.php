<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Message;
use App\Manager\Module\Message\Enum\State;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class UnreadCountTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanRetrieveUnreadMessageCount(): void
    {
        $user = $this->createUserWithManager('manager@example.com', 'password');
        $manager = $user->getManager();

        // Create 3 unread messages
        for ($i = 1; $i <= 3; $i++) {
            $message = new Message();
            $message->setMessage("Unread Message $i");
            $message->setSubject("Subject $i");
            $message->setManager($manager);
            $message->setState(State::UNREAD);
            $this->messageRepository->persist($message);
        }

        // Create 2 read messages
        for ($i = 1; $i <= 2; $i++) {
            $message = new Message();
            $message->setMessage("Read Message $i");
            $message->setSubject("Subject $i");
            $message->setManager($manager);
            $message->setState(State::READ);
            $this->messageRepository->persist($message);
        }

        $this->messageRepository->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(3, $response['unreadCount']);
    }

    #[Test]
    public function itReturnsZeroIfNoUnreadMessagesExist(): void
    {
        $user = $this->createUserWithManager('no-unread@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Read Message");
        $message->setSubject("Subject");
        $message->setManager($manager);
        $message->setState(State::READ);
        $this->messageRepository->persist($message);
        $this->messageRepository->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $response['unreadCount']);
    }

    #[Test]
    public function itOnlyCountsMessagesBelongingToAuthenticatedManager(): void
    {
        $user1 = $this->createUserWithManager('manager1@example.com', 'password');
        $manager1 = $user1->getManager();

        $user2 = $this->createUserWithManager('manager2@example.com', 'password');
        $manager2 = $user2->getManager();

        // 2 unread for manager 1
        for ($i = 1; $i <= 2; $i++) {
            $msg = new Message();
            $msg->setMessage("Msg $i");
            $msg->setSubject("Subj $i");
            $msg->setManager($manager1);
            $msg->setState(State::UNREAD);
            $this->messageRepository->persist($msg);
        }

        // 5 unread for manager 2
        for ($i = 1; $i <= 5; $i++) {
            $msg = new Message();
            $msg->setMessage("Msg $i");
            $msg->setSubject("Subj $i");
            $msg->setManager($manager2);
            $msg->setState(State::UNREAD);
            $this->messageRepository->persist($msg);
        }

        $this->messageRepository->flush();

        $this->client->loginUser($user1);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(2, $response['unreadCount']);
    }

    #[Test]
    public function itUpdatesCountAfterMessageIsMarkedAsRead(): void
    {
        $user = $this->createUserWithManager('manager-read@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Initially Unread");
        $message->setSubject("Subject");
        $message->setManager($manager);
        $message->setState(State::UNREAD);
        $this->messageRepository->persist($message);
        $this->messageRepository->flush();

        $uuid = $message->getUuid();

        $this->client->loginUser($user);

        // Check initial count
        $this->client->request('GET', '/de/messages/unread-count');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $response['unreadCount']);

        // View message (marks as read)
        $this->client->request('GET', "/de/messages/$uuid/view");
        self::assertResponseIsSuccessful();

        // Check count again
        $this->client->request('GET', '/de/messages/unread-count');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $response['unreadCount']);
    }

    #[Test]
    public function itDisplaysUnreadBadgeInSidebar(): void
    {
        $user = $this->createUserWithManager('manager-badge@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Unread Message");
        $message->setSubject("Subject");
        $message->setManager($manager);
        $message->setState(State::UNREAD);
        $this->messageRepository->persist($message);
        $this->messageRepository->flush();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/de/messages');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.pc-badge', '1');
    }

    #[Test]
    public function itUpdatesCountAfterMessageIsMarkedAsUnread(): void
    {
        $user = $this->createUserWithManager('manager-unread@example.com', 'password');
        $manager = $user->getManager();

        $message = new Message();
        $message->setMessage("Initially Read");
        $message->setSubject("Subject");
        $message->setManager($manager);
        $message->setState(State::READ);
        $this->messageRepository->persist($message);
        $this->messageRepository->flush();

        $uuid = $message->getUuid();

        $this->client->loginUser($user);

        // Check initial count
        $this->client->request('GET', '/de/messages/unread-count');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $response['unreadCount']);

        // Mark as unread
        $csrfToken = self::getContainer()->get(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class)->getToken('app_message_unread')->getValue();

        $this->client->request('POST', "/de/messages/$uuid/unread", [], [], [
            'HTTP_X-CSRF-TOKEN' => $csrfToken,
        ]);
        self::assertResponseIsSuccessful();

        // Check count again
        $this->client->request('GET', '/de/messages/unread-count');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(1, $response['unreadCount']);
    }
}
