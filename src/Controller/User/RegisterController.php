<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Manager\Framework\Command\CommandBus;
use App\Manager\Module\User\Command\UserRegistration\UserRegisterCommand;
use App\Manager\Module\User\Form\UserRegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class RegisterController extends AbstractController
{
    #[Route('/register', name: 'user_register', methods: [Request::METHOD_POST])]
    public function index(
        Request $request,
        CommandBus $commandBus,
        string $_locale
    ): Response
    {
        $userRegisterCommand = new UserRegisterCommand();

        $form = $this->createForm(UserRegisterFormType::class, $userRegisterCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandBus->handle($userRegisterCommand);
            return $this->redirectToRoute('user_landing_page', ['_locale' => $_locale]);
        }

        return $this->render('landing/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
