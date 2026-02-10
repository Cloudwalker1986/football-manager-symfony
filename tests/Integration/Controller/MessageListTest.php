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
        $user = $this->createUserWithManager('manager@example.com', 'password');
        $manager = $user->getManager();

        // Create some messages
        for ($i = 1; $i <= 15; $i++) {
            $message = new Message();
            $message->setMessage("Message $i");
            $message->setSubject("Subject $i");
            $message->setManager($manager);
            $this->messageRepository->persist($message);
        }
        $this->messageRepository->flush();

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
        $user = $this->createUserWithManager('manager-ajax@example.com', 'password');
        $manager = $user->getManager();

        for ($i = 1; $i <= 5; $i++) {
            $message = new Message();
            $message->setMessage("Message $i");
            $message->setSubject("Subject $i");
            $message->setManager($manager);
            $this->messageRepository->persist($message);
        }
        $this->messageRepository->flush();

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
        $user = $this->createUserWithManager('manager-pagination@example.com', 'password');
        $manager = $user->getManager();

        // Create 15 messages
        for ($i = 1; $i <= 15; $i++) {
            $message = new Message();
            $message->setMessage(sprintf("Message %02d", $i)); // Message 01, 02...
            $message->setSubject(sprintf("Subject %02d", $i));
            $message->setManager($manager);
            $this->messageRepository->persist($message);
        }
        $this->messageRepository->flush();

        $this->client->loginUser($user);

        // First page
        $crawler = $this->client->request('GET', '/de/messages?limit=10&offset=0');
        self::assertCount(10, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(1)', 'Subject 01');

        // Second page
        $crawler = $this->client->request('GET', '/de/messages?limit=10&offset=10');
        self::assertCount(5, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr:first-child td:nth-child(1)', 'Subject 11');

        // Check pagination links
        self::assertCount(3, $crawler->filter('.pagination .page-item:not(.disabled) .page-link')); // Previous, Page 1, Page 2 (wait, Page 2 is active, but its link is also there)
        self::assertSelectorExists('.pagination .page-item.active:contains("2")');
        self::assertSelectorExists('.pagination .page-item.disabled:contains("»")');
    }

    #[Test]
    public function itOnlyShowsMessagesBelongingToAuthenticatedManager(): void
    {
        $user1 = $this->createUserWithManager('manager1@example.com', 'password');
        $manager1 = $user1->getManager();

        $user2 = $this->createUserWithManager('manager2@example.com', 'password');
        $manager2 = $user2->getManager();

        $msg1 = new Message();
        $msg1->setMessage("Manager 1 Message");
        $msg1->setSubject("Manager 1 Subject");
        $msg1->setManager($manager1);
        $this->messageRepository->persist($msg1);

        $msg2 = new Message();
        $msg2->setMessage("Manager 2 Message");
        $msg2->setSubject("Manager 2 Subject");
        $msg2->setManager($manager2);
        $this->messageRepository->persist($msg2);

        $this->messageRepository->flush();

        $this->client->loginUser($user1);
        $crawler = $this->client->request('GET', '/de/messages');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody tr td:nth-child(1)', 'Manager 1 Subject');
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
        $user = $this->createUserWithManager('manager-filter@example.com', 'password');
        $manager = $user->getManager();

        // Create 2 read and 3 unread messages
        for ($i = 1; $i <= 5; $i++) {
            $message = new Message();
            $message->setMessage("Message $i");
            $message->setSubject("Subject $i");
            $message->setManager($manager);
            if ($i <= 2) {
                $message->setState(\App\Manager\Module\Message\Enum\State::READ);
            } else {
                $message->setState(\App\Manager\Module\Message\Enum\State::UNREAD);
            }
            $this->messageRepository->persist($message);
        }
        $this->messageRepository->flush();

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
