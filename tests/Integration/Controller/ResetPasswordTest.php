<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\ResetPasswordRequest;
use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class ResetPasswordTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanRequestPasswordReset(): void
    {
        $user = $this->createUserWithManager('reset-test@example.com', 'old-password');

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
        $user = $this->createUserWithManager('reset-complete@example.com', 'old-password');

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
        $user = $this->createUserWithManager('expired@example.com', 'old-password');

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
