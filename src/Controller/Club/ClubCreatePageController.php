<?php

declare(strict_types=1);

namespace App\Controller\Club;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ClubCreatePageController extends BaseController
{
    #[Route('/wizard/club', name: 'app_wizard_club', methods: ['GET'])]
    public function index(): Response
    {
        $manager = $this->getUser()->getManager();

        if ($manager && $manager->getClub()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('wizard/club/index.html.twig');
    }
}
