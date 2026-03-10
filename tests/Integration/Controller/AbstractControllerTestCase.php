<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\Manager;
use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\ManagerRepository;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractControllerTestCase extends WebTestCase
{
    use RefreshDatabaseTrait;

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
    }

    protected function tearDown(): void
    {
        $this->client = null;
        parent::tearDown();
    }

    protected function createUserWithManager(
        string $email = 'test@example.com',
        string $password = 'password123',
        string $managerName = 'Test Manager',
        Status $status = Status::VERIFIED,
        string $locale = 'de'
    ): User {
        $container = self::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmailAddress($email);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setStatus($status);
        $user->setLocale($locale);

        $manager = new Manager();
        $manager->setName($managerName);
        $user->setManager($manager);

        $userRepository->persist($user);
        $userRepository->flush();

        return $user;
    }
}
