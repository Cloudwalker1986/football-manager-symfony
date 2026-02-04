<?php

declare(strict_types=1);

namespace App\IntegrationTests\Controller;

use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration-tests')]
class LoginTest extends AbstractControllerTestCase
{
    #[Test]
    public function itCanLoginWithValidCredentials(): void
    {
        $this->createUserWithManager('login-test@example.com', 'password123');

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
        $this->createUserWithManager('invalid-pass@example.com', 'password123');

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
        $this->createUserWithManager('unverified@example.com', 'password123', 'Test Manager', Status::NOT_VERIFIED);

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
        $user = $this->createUserWithManager('logout-test@example.com', 'password123');

        $this->client->loginUser($user);

        $this->client->request('GET', '/de/dashboard');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/de/logout');
        self::assertResponseRedirects('/de/');
        $this->client->followRedirect();
        self::assertRouteSame('user_landing_page');
    }
}
