<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Group('integration-tests')]
class LoginTest extends WebTestCase
{
    private ?UserRepository $userRepository;
    private ?UserPasswordHasherInterface $passwordHasher;
    private $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        $this->client = null;
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $tables = ['reset_password_request', 'user_verification', 'manager', 'user'];
        foreach ($tables as $table) {
            try {
                $connection->executeStatement(sprintf('DELETE FROM %s', $table));
            } catch (\Exception $e) {
                // Intentionally ignore exceptions during test cleanup; missing tables or
                // cleanup failures should not cause the tests themselves to fail.
            }
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Test]
    public function itCanLoginWithValidCredentials(): void
    {
        $user = new User();
        $user->setEmailAddress('login-test@example.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $user->setStatus(Status::VERIFIED);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $crawler = $this->client->request('GET', '/de/login');
        // echo $this->client->getResponse()->getContent();
        $form = $crawler->filter('form[action="/de/login"]')->form([
            '_username' => 'login-test@example.com',
            '_password' => 'password123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/dashboard');
        $this->client->followRedirect();
        self::assertSelectorTextContains('h5', 'Dashboard');
        self::assertSelectorTextContains('p', 'Welcome to the football manager game, login-test@example.com!');
    }

    #[Test]
    public function itCannotLoginWithInvalidPassword(): void
    {
        $user = new User();
        $user->setEmailAddress('invalid-pass@example.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $user->setStatus(Status::VERIFIED);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $crawler = $this->client->request('GET', '/de/login');
        $form = $crawler->filter('form[action="/de/login"]')->form([
            '_username' => 'invalid-pass@example.com',
            '_password' => 'wrong-password',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-danger');
    }

    #[Test]
    public function itCannotLoginWithUnverifiedAccount(): void
    {
        $user = new User();
        $user->setEmailAddress('unverified@example.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $user->setStatus(Status::NOT_VERIFIED);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $crawler = $this->client->request('GET', '/de/login');
        $form = $crawler->filter('form[action="/de/login"]')->form([
            '_username' => 'unverified@example.com',
            '_password' => 'password123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Your user account is not activated.');
    }

    #[Test]
    public function itCanLogout(): void
    {
        $user = new User();
        $user->setEmailAddress('logout-test@example.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $user->setStatus(Status::VERIFIED);

        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/de/dashboard');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/de/logout');
        self::assertResponseRedirects('/de/');
        $this->client->followRedirect();
        self::assertRouteSame('user_landing_page');
    }
}
