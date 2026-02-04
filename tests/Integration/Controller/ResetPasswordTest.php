<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Group('integration-tests')]
class ResetPasswordTest extends WebTestCase
{
    private ?UserRepository $userRepository;
    private ?ResetPasswordRequestRepository $resetPasswordRequestRepository;
    private ?UserPasswordHasherInterface $passwordHasher;
    private $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
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
                // Ignore if table doesn't exist yet
            }
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    #[Test]
    public function itCanRequestPasswordReset(): void
    {
        $user = new User();
        $user->setEmailAddress('reset-test@example.com');
        $user->setPassword('old-password');
        $user->setStatus(Status::VERIFIED);
        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $crawler = $this->client->request('GET', '/de/forgot-password');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('E-Mail zum Zurücksetzen senden')->form([
            'form[email]' => 'reset-test@example.com',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/forgot-password/check-email');
        $this->client->followRedirect();
        self::assertSelectorTextContains('h3', 'E-Mail zum Zurücksetzen des Passworts gesendet');

        // Verify request exists in DB
        $resetRequest = $this->resetPasswordRequestRepository->findOneBy(['user' => $user]);
        self::assertNotNull($resetRequest);
    }

    #[Test]
    public function itGivesGenericResponseForNonExistentEmail(): void
    {
        $crawler = $this->client->request('GET', '/de/forgot-password');
        $form = $crawler->selectButton('E-Mail zum Zurücksetzen senden')->form([
            'form[email]' => 'non-existent@example.com',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/forgot-password/check-email');
        $this->client->followRedirect();
        self::assertSelectorTextContains('h3', 'E-Mail zum Zurücksetzen des Passworts gesendet');
    }

    #[Test]
    public function itCanResetPasswordWithValidToken(): void
    {
        $user = new User();
        $user->setEmailAddress('reset-complete@example.com');
        $user->setPassword('old-password');
        $user->setStatus(Status::VERIFIED);
        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $selector = 'test_selector';
        $verifier = 'test_verifier';
        $hashedToken = password_hash($verifier, PASSWORD_BCRYPT);
        $expiresAt = new \DateTimeImmutable('+1 hour');

        $resetRequest = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
        $this->resetPasswordRequestRepository->persist($resetRequest);
        $this->resetPasswordRequestRepository->flush();

        $crawler = $this->client->request('GET', sprintf('/de/reset-password/%s/%s', $selector, $verifier));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Passwort zurücksetzen')->form([
            'form[password][first]' => 'new-password123',
            'form[password][second]' => 'new-password123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        // Verify password updated
        $updatedUser = $this->userRepository->findOneBy(['emailAddress' => 'reset-complete@example.com']);
        self::assertTrue($this->passwordHasher->isPasswordValid($updatedUser, 'new-password123'));

        // Verify token invalidated
        self::getContainer()->get('doctrine')->getManager()->clear();
        $invalidatedRequest = $this->resetPasswordRequestRepository->findOneBy(['selector' => $selector]);
        self::assertNull($invalidatedRequest);
    }

    #[Test]
    public function itCannotResetWithExpiredToken(): void
    {
        $user = new User();
        $user->setEmailAddress('expired@example.com');
        $user->setPassword('old-password');
        $user->setStatus(Status::VERIFIED);
        $this->userRepository->persist($user);
        $this->userRepository->flush();

        $selector = 'expired_selector';
        $verifier = 'expired_verifier';
        $hashedToken = password_hash($verifier, PASSWORD_BCRYPT);
        $expiresAt = new \DateTimeImmutable('-1 minute');

        $resetRequest = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
        $this->resetPasswordRequestRepository->persist($resetRequest);
        $this->resetPasswordRequestRepository->flush();

        $this->client->request('GET', sprintf('/de/reset-password/%s/%s', $selector, $verifier));

        self::assertResponseRedirects('/de/forgot-password');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-danger');
    }
}
