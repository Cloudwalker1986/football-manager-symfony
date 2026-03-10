<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class UnreadCountTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanRetrieveUnreadMessageCount(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-9@example.com']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(3, $response['unreadCount']);
    }

    #[Test]
    public function itReturnsZeroIfNoUnreadMessagesExist(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-13@example.com']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(0, $response['unreadCount']);
    }

    #[Test]
    public function itOnlyCountsMessagesBelongingToAuthenticatedManager(): void
    {
        $user1 = $this->userRepository->findOneBy(['emailAddress' => 'manager-7@example.com']);

        $this->client->loginUser($user1);
        $this->client->request('GET', '/de/messages/unread-count');

        self::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals(5, $response['unreadCount']);
    }

    #[Test]
    public function itUpdatesCountAfterMessageIsMarkedAsRead(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-8@example.com']);
        $message = $this->messageRepository->findOneBy(['subject' => 'To Mark as Read']);

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
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-6@example.com']);

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/de/messages');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.pc-badge', '15');
    }

    #[Test]
    public function itUpdatesCountAfterMessageIsMarkedAsUnread(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-10@example.com']);
        $message = $this->messageRepository->findOneBy(['subject' => 'To Mark as Unread']);

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
