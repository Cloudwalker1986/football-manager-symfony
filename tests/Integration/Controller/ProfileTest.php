<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class ProfileTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanUpdateProfile(): void
    {
        $user = $this->createUserWithManager('profile-test@example.com', 'password123', 'Old Manager Name', Status::VERIFIED, 'de');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/de/profile');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('button[type="submit"]')->form([
            'update_profile_form[email]' => 'updated@example.com',
            'update_profile_form[managerName]' => 'New Manager Name',
            'update_profile_form[locale]' => 'en',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/en/profile');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('h5.m-b-10', 'User Profile');
        self::assertSelectorTextContains('.card-header h5', 'User Details');

        $updatedUser = $this->userRepository->findOneBy(['emailAddress' => 'updated@example.com']);
        self::assertNotNull($updatedUser);
        self::assertEquals('en', $updatedUser->getLocale());
        self::assertEquals('New Manager Name', $updatedUser->getManager()->getName());
    }

    #[Test]
    public function itCanChangePassword(): void
    {
        $user = $this->createUserWithManager('password-change@example.com', 'old-password', 'Some Manager');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/de/profile');
        $form = $crawler->filter('button[type="submit"]')->form([
            'update_profile_form[currentPassword]' => 'old-password',
            'update_profile_form[newPassword][first]' => 'new-password-123',
            'update_profile_form[newPassword][second]' => 'new-password-123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/profile');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        $updatedUser = $this->userRepository->findOneBy(['emailAddress' => 'password-change@example.com']);
        self::assertTrue($this->passwordHasher->isPasswordValid($updatedUser, 'new-password-123'));
    }

    #[Test]
    public function itShowsErrorOnInvalidCurrentPassword(): void
    {
        $user = $this->createUserWithManager('wrong-pass-test@example.com', 'correct-password', 'Some Manager');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/de/profile');
        $form = $crawler->filter('button[type="submit"]')->form([
            'update_profile_form[currentPassword]' => 'wrong-password',
            'update_profile_form[newPassword][first]' => 'new-password-123',
            'update_profile_form[newPassword][second]' => 'new-password-123',
        ]);

        $this->client->submit($form);

        self::assertResponseIsSuccessful();
        // echo $this->client->getResponse()->getContent();
        self::assertSelectorExists('.invalid-feedback');
    }

    #[Test]
    public function itCanDeleteAccount(): void
    {
        $user = $this->createUserWithManager('delete-me@example.com', 'password123', 'To Be Deleted');
        $manager = $user->getManager();

        // Create a reset password request to test non-cascading deletion
        $resetRequest = new ResetPasswordRequest(
            $user,
            new \DateTimeImmutable('+1 hour'),
            'selector',
            'hashedToken'
        );
        $this->resetPasswordRequestRepository->persist($resetRequest);
        $this->resetPasswordRequestRepository->flush();

        $userId = $user->getId();
        $managerId = $manager->getId();

        $this->client->loginUser($user);

        // First, check if the "Delete Account" button is present
        $crawler = $this->client->request('GET', '/de/profile');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button.btn-danger');

        // Submit the delete form
        $deleteFormCrawler = $crawler->filter('form[action$="/profile/delete"]');
        $form = $deleteFormCrawler->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/de/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        // Verify data is deleted
        $deletedUser = $this->userRepository->find($userId);
        self::assertNull($deletedUser);

        $deletedManager = $this->managerRepository->find($managerId);
        self::assertNull($deletedManager);

        $resetRequests = $this->resetPasswordRequestRepository->findBy(['user' => $user]);
        self::assertEmpty($resetRequests);
    }
}
