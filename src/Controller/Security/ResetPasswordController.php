<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Manager\Module\Notification\Message\PasswordResetSuccess;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $messageBus,
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/reset-password/{selector}/{verifier}', name: 'app_reset_password')]
    public function reset(Request $request, string $selector, string $verifier): Response
    {
        $resetRequest = $this->resetPasswordRequestRepository->findOneBy(['selector' => $selector]);

        if (null === $resetRequest || $resetRequest->isExpired() || !password_verify($verifier, $resetRequest->getHashedToken())) {
            $this->addFlash('error', $this->translator->trans('login.reset_password.invalid_token', [], 'messages'));
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'registration.password.label'],
                'second_options' => ['label' => 'registration.repeat_password.label'],
                'invalid_message' => 'registration.password.mismatch',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $resetRequest->getUser();

            // Hash the new password
            $newPassword = $this->passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($newPassword);

            // Invalidate the token
            $this->resetPasswordRequestRepository->remove($resetRequest);

            // Optionally remove all other tokens for this user
            $this->resetPasswordRequestRepository->removeUserToken($user);

            $this->userRepository->flush();

            $this->messageBus->dispatch(new PasswordResetSuccess($user->getUuid()));

            $this->addFlash('success', $this->translator->trans('login.reset_password.success', [], 'messages'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
