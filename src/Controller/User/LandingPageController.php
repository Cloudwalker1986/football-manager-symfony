<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Manager\Module\User\Form\UserRegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LandingPageController extends AbstractController
{
    #[Route(
        '/',
        name: 'user_landing_page',
        methods: [Request::METHOD_GET]
    )]
    public function index(): Response
    {
        $form = $this->createForm(UserRegisterFormType::class);

        return $this->render('landing/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
