<?php

declare(strict_types=1);

namespace Controller\Club\Api\Wizard;

use App\Entity\FootballAssociation;
use App\Entity\League;
use App\Entity\Manager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ClubWizardControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testWizardFlow(): void
    {
        // 1. Setup Data - Use data from fixtures
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailAddress' => 'manager@example.com']);
        $association = $this->entityManager->getRepository(FootballAssociation::class)->findOneBy(['name' => 'Test FA']);
        $league = $this->entityManager->getRepository(League::class)->findOneBy(['name' => 'Test League']);

        $this->client->loginUser($user);

        // 2. Check Status
        $this->client->request('GET', '/de/api/wizard/club/status');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['showWizard']);
        $this->assertEquals('association', $data['currentStep']);

        // 3. Step 1: Select Association
        $this->client->request('POST', '/de/api/wizard/club/association', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'associationId' => $association->getUuid()
        ]));
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('league', $data['step']);

        // 4. Step 2: Select League
        $this->client->request('POST', '/de/api/wizard/club/league', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'leagueId' => $league->getUuid()
        ]));
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('specification', $data['step']);

        // 5. Step 3: Club Specification
        $this->client->request('POST', '/de/api/wizard/club/specification', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Wizard Club',
            'shortName' => 'Wiz'
        ]));
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertTrue($data['completed']);
        $this->assertTrue($data['reload']);

        // 6. Verify final state
        $this->entityManager->clear();
        $updatedManager = $this->entityManager->getRepository(Manager::class)->findOneBy(['name' => 'Wizard Manager']);
        $this->assertNotNull($updatedManager->getClub());
        $this->assertEquals('Wizard Club', $updatedManager->getClub()->getName());
        $this->assertCount(1, $updatedManager->getClub()->getTeams());
        $this->assertEquals($league->getId(), $updatedManager->getClub()->getTeams()->first()->getLeague()->getId());

        // 6.1 Verify message creation
        $messages = $this->entityManager->getRepository(\App\Entity\Message::class)->findBy(['manager' => $updatedManager]);
        $this->assertCount(1, $messages);

        // Use translator to check the expected translated subject
        $translator = static::getContainer()->get(\Symfony\Contracts\Translation\TranslatorInterface::class);
        $expectedSubject = $translator->trans('wizard.club.creation.message.subject', [], 'messages', 'de');
        $expectedBody = $translator->trans('wizard.club.creation.message.body', ['%club_name%' => 'Wizard Club'], 'messages', 'de');

        // The stored value is the key
        $this->assertEquals('wizard.club.creation.message.subject', $messages[0]->getSubject());

        // If we want to test the translation on load, we should call the view endpoint
        $this->client->request('GET', "/de/messages/{$messages[0]->getUuid()}/view");
        $this->assertResponseIsSuccessful();
        $messageData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($expectedSubject, $messageData['subject']);
        $this->assertStringContainsString($expectedBody, $messageData['message']);

        // 7. Check status again - should not show wizard
        $this->client->request('GET', '/de/api/wizard/club/status');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['showWizard']);
    }

    public function testLeagueFullValidation(): void
    {
        // 1. Setup Data - Use data from fixtures
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailAddress' => 'manager-2@example.com']);
        $association = $this->entityManager->getRepository(FootballAssociation::class)->findOneBy(['name' => 'Full FA']);
        $league = $this->entityManager->getRepository(League::class)->findOneBy(['name' => 'Full League']);

        $this->client->loginUser($user);

        // Set association in wizard state
        $this->client->request('POST', '/de/api/wizard/club/association', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'associationId' => $association->getUuid()
        ]));
        $this->assertResponseIsSuccessful();

        // Attempt to select full league
        $this->client->request('POST', '/de/api/wizard/club/league', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'leagueId' => $league->getUuid()
        ]));

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('leagueId', $data['errors']);
        $this->assertEquals('Diese Liga ist bereits voll.', $data['errors']['leagueId'][0]);
    }

    public function testUniqueClubNameValidation(): void
    {
        // 1. Setup Data - Use data from fixtures
        $association = $this->entityManager->getRepository(FootballAssociation::class)->findOneBy(['name' => 'Unique FA']);
        $league = $this->entityManager->getRepository(League::class)->findOneBy(['name' => 'Unique League']);

        // Create a new user for the wizard (using user_3 from fixtures)
        $newUser = $this->entityManager->getRepository(User::class)->findOneBy(['emailAddress' => 'deleteable-manager-3@example.com']);
        $this->client->loginUser($newUser);

        // Progress through the wizard
        $this->client->request('POST', '/de/api/wizard/club/association', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'associationId' => $association->getUuid()
        ]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/de/api/wizard/club/league', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'leagueId' => $league->getUuid()
        ]));
        $this->assertResponseIsSuccessful();

        // Attempt to create a club with the same name (Existing Club is created via fixtures in Club.yaml)
        $this->client->request('POST', '/de/api/wizard/club/specification', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Existing Club',
            'shortName' => 'NEW'
        ]));

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('name', $data['errors']);
        $this->assertEquals('Ein Club mit diesem Namen existiert bereits.', $data['errors']['name'][0]);
    }
}
