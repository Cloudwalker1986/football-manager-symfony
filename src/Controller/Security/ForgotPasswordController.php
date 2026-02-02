<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Entity\ResetPasswordRequest;
use App\Manager\Module\Notification\Message\SendPasswordResetEmail;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\User\RemoveUserTokenInterface;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractController
{
    private const int EXPIRE_TIME_IN_MINUTES = 60;

    public function __construct(
        private UserRepository $userRepository,
        private CreateEntityInterface&RemoveUserTokenInterface $resetPasswordRequestRepository,
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'login.reset_password.email_label',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processForgotPasswordRequest($form->get('email')->getData());
        }

        return $this->render('security/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/forgot-password/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('security/reset_password/check_email.html.twig', [
            'resetTokenLifetime' => self::EXPIRE_TIME_IN_MINUTES,
        ]);
    }

    private function processForgotPasswordRequest(string $emailAddress): Response
    {
        $user = $this->userRepository->findOneBy(['emailAddress' => $emailAddress]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        // Remove old reset requests
        $this->resetPasswordRequestRepository->removeUserToken($user);

        // Generate a new reset token
        $selector = bin2hex(random_bytes(8));
        $verifier = bin2hex(random_bytes(32));
        $hashedToken = password_hash($verifier, PASSWORD_BCRYPT);

        $expiresAt = new \DateTimeImmutable('+1 hour');

        $resetPasswordRequest = new ResetPasswordRequest(
            $user,
            $expiresAt,
            $selector,
            $hashedToken
        );

        $this->resetPasswordRequestRepository
            ->persist($resetPasswordRequest)
            ->flush();

        $this->messageBus->dispatch(
            new SendPasswordResetEmail(
                $user->getUuid(),
                $selector,
                $verifier,
                self::EXPIRE_TIME_IN_MINUTES
            )
        );

        return $this->redirectToRoute('app_check_email');
    }
}
