<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Manager;
use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\ManagerRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractControllerTestCase extends WebTestCase
{
    protected ?KernelBrowser $client;
    protected ?UserRepository $userRepository;
    protected ?ManagerRepository $managerRepository;
    protected ?\App\Repository\MessageRepository $messageRepository;
    protected ?ResetPasswordRequestRepository $resetPasswordRequestRepository;
    protected ?UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->managerRepository = $container->get(ManagerRepository::class);
        $this->messageRepository = $container->get(\App\Repository\MessageRepository::class);
        $this->resetPasswordRequestRepository = $container->get(ResetPasswordRequestRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        $this->client = null;
        parent::tearDown();
    }

    protected function cleanupDatabase(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $tables = ['message', 'reset_password_request', 'user_verification', 'manager', 'user'];
        foreach ($tables as $table) {
            try {
                $connection->executeStatement(sprintf('DELETE FROM %s', $table));
            } catch (\Exception $e) {
                // Intentionally ignore exceptions during test cleanup
            }
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function createUserWithManager(
        string $email = 'test@example.com',
        string $password = 'password123',
        string $managerName = 'Test Manager',
        Status $status = Status::VERIFIED,
        string $locale = 'de'
    ): User {
        $user = new User();
        $user->setEmailAddress($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setStatus($status);
        $user->setLocale($locale);

        $manager = new Manager();
        $manager->setName($managerName);
        $user->setManager($manager);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        return $user;
    }
}
