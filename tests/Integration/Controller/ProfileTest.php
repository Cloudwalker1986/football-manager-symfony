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
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager@example.com']);
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

        self::getContainer()->get('doctrine')->getManager()->clear();
        $updatedUser = $this->userRepository->findOneBy(['emailAddress' => 'updated@example.com']);
        self::assertNotNull($updatedUser);
        self::assertEquals('en', $updatedUser->getLocale());
        self::assertEquals('New Manager Name', $updatedUser->getManager()->getName());
    }

    #[Test]
    public function itCanChangePassword(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-2@example.com']);

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/de/profile');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('button[type="submit"]')->form([
            'update_profile_form[currentPassword]' => 'password',
            'update_profile_form[newPassword][first]' => 'new-password-123',
            'update_profile_form[newPassword][second]' => 'new-password-123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/de/profile');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        self::getContainer()->get('doctrine')->getManager()->clear();
        $updatedUser = $this->userRepository->findOneBy(['emailAddress' => 'manager-2@example.com']);
        self::assertTrue($this->passwordHasher->isPasswordValid($updatedUser, 'new-password-123'));
    }

    #[Test]
    public function itShowsErrorOnInvalidCurrentPassword(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-4@example.com']);

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/de/profile');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('button[type="submit"]')->form([
            'update_profile_form[currentPassword]' => 'wrong-password',
            'update_profile_form[newPassword][first]' => 'new-password-123',
            'update_profile_form[newPassword][second]' => 'new-password-123',
        ]);

        $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.invalid-feedback');
    }

    #[Test]
    public function itCanDeleteAccount(): void
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => 'manager-5@example.com']);
        $manager = $user->getManager();

        $userId = $user->getId();
        $managerId = $manager->getId();

        $this->client->loginUser($user);

        // First, check if the "Delete Account" button is present
        $crawler = $this->client->request('GET', '/de/profile');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button.btn-danger');

        // Submit the delete form
        $deleteForm = $crawler->filter('form[action="/de/profile/delete"]')->form();
        $this->client->submit($deleteForm);

        self::assertResponseRedirects('/de/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        // Verify data is deleted
        self::getContainer()->get('doctrine')->getManager()->clear();
        $deletedUser = $this->userRepository->find($userId);
        self::assertNull($deletedUser);

        $deletedManager = $this->managerRepository->find($managerId);
        self::assertNull($deletedManager);
    }
}
