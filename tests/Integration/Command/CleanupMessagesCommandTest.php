<?php

declare(strict_types=1);

namespace App\IntegrationTests\Command;

use App\Entity\Message;
use App\IntegrationTests\Controller\AbstractControllerTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class CleanupMessagesCommandTest extends AbstractControllerTestCase
{
    #[Test]
    public function itDeletesOldMessages(): void
    {
        $user = $this->createUserWithManager('cleanup@example.com', 'password');
        $manager = $user->getManager();

        // Message 1: 91 days old (should be deleted)
        $oldMessage = new Message();
        $oldMessage->setMessage('Old message');
        $oldMessage->setManager($manager);
        // Reflection to set createdAt because it's managed by DateTimeStamper
        $this->setCreatedAt($oldMessage, new \DateTimeImmutable('-91 days'));
        $this->messageRepository->persist($oldMessage);

        // Message 2: 89 days old (should NOT be deleted)
        $recentMessage = new Message();
        $recentMessage->setMessage('Recent message');
        $recentMessage->setManager($manager);
        $this->setCreatedAt($recentMessage, new \DateTimeImmutable('-89 days'));
        $this->messageRepository->persist($recentMessage);

        $this->messageRepository->flush();

        $application = new Application(self::$kernel);
        $command = $application->find('app:cleanup:messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Successfully deleted 1 old message(s).', $output);

        // Verify database
        $this->messageRepository->getEntityManager()->clear();

        $allMessages = $this->messageRepository->findAll();
        self::assertCount(1, $allMessages);
        self::assertEquals('Recent message', $allMessages[0]->getMessage());
    }

    #[Test]
    public function itDoesNothingIfNoOldMessages(): void
    {
        $user = $this->createUserWithManager('cleanup2@example.com', 'password');
        $manager = $user->getManager();

        $recentMessage = new Message();
        $recentMessage->setMessage('Recent message');
        $recentMessage->setManager($manager);
        $this->setCreatedAt($recentMessage, new \DateTimeImmutable('-10 days'));
        $this->messageRepository->persist($recentMessage)->flush();

        $application = new Application(self::$kernel);
        $command = $application->find('app:cleanup:messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('No old messages found.', $commandTester->getDisplay());

        $allMessages = $this->messageRepository->findAll();
        self::assertCount(1, $allMessages);
    }

    private function setCreatedAt(Message $message, \DateTimeImmutable $date): void
    {
        $reflection = new \ReflectionClass($message);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($message, $date);
    }
}
