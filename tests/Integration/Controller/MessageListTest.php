<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Message;
use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class MessageListTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanListMessagesForAuthenticatedManager(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-6@example.com']);

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/de/messages');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#message-list-container');
        // Default limit is 10
        self::assertCount(10, $crawler->filter('table tbody tr'));
        // Order is ASC by createdAt. Subject is in the first column.
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(1)', 'Subject 1');
    }

    #[Test]
    public function itCanListMessagesViaAjax(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-7@example.com']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/de/messages?ajax=1');

        self::assertResponseIsSuccessful();
        // Ajax response should NOT contain the layout, just the list content
        self::assertSelectorNotExists('.row');
        self::assertSelectorExists('#message-table');
        self::assertCount(5, $this->client->getCrawler()->filter('table tbody tr'));
    }

    #[Test]
    public function itCanPaginateMessages(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-6@example.com']);

        $this->client->loginUser($user);

        // First page
        $crawler = $this->client->request('GET', '/de/messages?limit=10&offset=0');
        self::assertCount(10, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(1)', 'Subject 1');

        // Second page
        $crawler = $this->client->request('GET', '/de/messages?limit=10&offset=10');
        self::assertCount(5, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(1)', 'Subject 11');

        // Check pagination links
        self::assertCount(3, $crawler->filter('.pagination .page-item:not(.disabled) .page-link'));
        self::assertSelectorExists('.pagination .page-item.active:contains("2")');
        self::assertSelectorExists('.pagination .page-item.disabled:contains("»")');
    }

    #[Test]
    public function itOnlyShowsMessagesBelongingToAuthenticatedManager(): void
    {
        $user1 = $this->userRepository->findOneBy(['emailAddress' => 'manager-6@example.com']);
        $user2 = $this->userRepository->findOneBy(['emailAddress' => 'manager-7@example.com']);

        $this->client->loginUser($user1);
        $crawler = $this->client->request('GET', '/de/messages');

        self::assertResponseIsSuccessful();
        self::assertCount(10, $crawler->filter('table tbody tr')); // manager-6 has 15 messages, limit 10
        self::assertSelectorTextContains('table tbody tr td:nth-child(1)', 'Subject 1');
        self::assertSelectorTextNotContains('table tbody tr td:nth-child(1)', 'Manager 2 Subject');
    }

    #[Test]
    public function itRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/de/messages');
        self::assertResponseRedirects('/de/login');
    }

    #[Test]
    public function itCanFilterUnreadMessages(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-9@example.com']);

        $this->client->loginUser($user);

        // All messages
        $crawler = $this->client->request('GET', '/de/messages');
        self::assertCount(5, $crawler->filter('table tbody tr'));

        // Filter unread
        $crawler = $this->client->request('GET', '/de/messages?state=unread');
        self::assertResponseIsSuccessful();
        self::assertCount(3, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(4)', 'Ungelesen');
    }
}
