<?php

declare(strict_types=1);

namespace App\Controller\Club\Api\Wizard;

use App\Controller\BaseController;
use App\Entity\Club;
use App\Entity\Team;
use App\Form\Wizard\ClubSpecificationType;
use App\Manager\Module\Club\Event\ClubCreated;
use App\Manager\Module\Club\Wizard\ClubWizardState;
use App\Manager\Module\Team\Enum\TeamType;
use App\Repository\FootballAssociationRepository;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/wizard/club', name: 'api_wizard_club_')]
class ClubCreationController extends BaseController
{
    public function __construct(
        private readonly ClubWizardState $wizardState,
        private readonly FootballAssociationRepository $associationRepository,
        private readonly LeagueRepository $leagueRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['showWizard' => false], 401);
        }

        $manager = $user->getManager();
        if (!$manager) {
            return new JsonResponse(['showWizard' => false]);
        }

        $showWizard = $manager->getClub() === null;
        $data = [
            'showWizard' => $showWizard,
            'currentStep' => $this->getCurrentStepName(),
        ];

        if ($showWizard) {
            $data['associations'] = array_map(fn($a) => [
                'uuid' => $a->getUuid(),
                'name' => $a->getName(),
            ], $this->associationRepository->findAll());

            $associationUuid = $this->wizardState->getAssociationId();
            if ($associationUuid) {
                $association = $this->associationRepository->findOneBy(['uuid' => $associationUuid]);
                if ($association) {
                    $data['leagues'] = array_map(fn($l) => [
                        'uuid' => $l->getUuid(),
                        'name' => $l->getName(),
                        'level' => $l->getLevel(),
                        'teamCount' => $this->leagueRepository->countTeams($l),
                    ], $association->getLeagues()->toArray());
                }
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/association', name: 'association', methods: ['POST'])]
    public function selectAssociation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $associationUuid = $data['associationId'] ?? null;

        if (!$associationUuid) {
            return $this->errorResponse(['associationId' => [$this->translator->trans('wizard.club.errors.association_required')]], 'association');
        }

        $association = $this->associationRepository->findOneBy(['uuid' => $associationUuid]);
        if (!$association) {
            return $this->errorResponse(['associationId' => [$this->translator->trans('wizard.club.errors.association_not_found')]], 'association');
        }

        $this->wizardState->setAssociationId($associationUuid);
        $this->wizardState->setLeagueId(''); // Clear league if association changes

        return $this->successResponse('league', ['associationId' => $associationUuid]);
    }

    #[Route('/league', name: 'league', methods: ['POST'])]
    public function selectLeague(Request $request): JsonResponse
    {
        $associationUuid = $this->wizardState->getAssociationId();
        if (!$associationUuid) {
            return $this->errorResponse(['associationId' => [$this->translator->trans('wizard.club.errors.select_association_first')]], 'association');
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $leagueUuid = $data['leagueId'] ?? null;

        if (!$leagueUuid) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.league_required')]], 'league');
        }

        $league = $this->leagueRepository->findOneBy(['uuid' => $leagueUuid]);
        if (!$league) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.league_not_found')]], 'league');
        }

        if ((string) $league->getAssociation()->getUuid() !== $associationUuid) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.league_wrong_association')]], 'league');
        }

        $teamCount = $this->leagueRepository->countTeams($league);
        if ($teamCount >= 16) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.league_full')]], 'league');
        }

        $this->wizardState->setLeagueId($leagueUuid);

        return $this->successResponse('specification', ['leagueId' => $leagueUuid]);
    }

    #[Route('/specification', name: 'specification', methods: ['POST'])]
    public function specifyClub(Request $request, FormFactoryInterface $formFactory): JsonResponse
    {
        $leagueUuid = $this->wizardState->getLeagueId();
        if (!$leagueUuid) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.select_league_first')]], 'league');
        }

        $league = $this->leagueRepository->findOneBy(['uuid' => $leagueUuid]);
        if (!$league) {
            return $this->errorResponse(['leagueId' => [$this->translator->trans('wizard.club.errors.league_not_found')]], 'league');
        }

        $form = $formFactory->createNamed('', ClubSpecificationType::class);

        $data = json_decode($request->getContent(), true) ?? [];
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->errorResponse($this->getFormErrors($form), 'specification');
        }

        $clubData = $form->getData();
        $manager = $this->getManager();

        $this->entityManager->beginTransaction();
        try {
            $club = new Club();
            $club->setName($clubData['name']);
            $club->setShortName($clubData['shortName']);
            $club->setBudget('1000000'); // Default budget
            $club->setManager($manager);

            $team = new Team();
            $team->setType(TeamType::FIRST_TEAM);
            $team->setClub($club);
            $team->setLeague($league);

            $this->entityManager->persist($club);
            $this->entityManager->persist($team);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->eventDispatcher->dispatch(new ClubCreated($club->getUuid()));
        } catch (\Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            return new JsonResponse(['success' => false, 'message' => $this->translator->trans('wizard.club.errors.failed_to_create')], 500);
        }

        $this->wizardState->clear();

        return new JsonResponse([
            'success' => true,
            'completed' => true,
            'reload' => true,
            'data' => [
                'clubId' => $club->getUuid(),
            ],
        ]);
    }

    #[Route('/reset/league', name: 'reset_league', methods: ['POST'])]
    public function resetLeague(): JsonResponse
    {
        $this->wizardState->setLeagueId(null);
        return new JsonResponse(['success' => true]);
    }

    #[Route('/reset/association', name: 'reset_association', methods: ['POST'])]
    public function resetAssociation(): JsonResponse
    {
        $this->wizardState->setAssociationId(null);
        $this->wizardState->setLeagueId(null);
        return new JsonResponse(['success' => true]);
    }

    private function getCurrentStepName(): string
    {
        if (!$this->wizardState->getAssociationId()) {
            return 'association';
        }
        if (!$this->wizardState->getLeagueId()) {
            return 'league';
        }
        return 'specification';
    }

    private function successResponse(string $nextStep, array $data): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'step' => $nextStep,
            'data' => $data,
        ]);
    }

    private function errorResponse(array $errors, string $currentStep): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => false,
                'step' => $currentStep,
                'errors' => $errors,
            ],
            422
        );
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()][] = $this->translator->trans($error->getMessage());
        }
        return $errors;
    }
}
