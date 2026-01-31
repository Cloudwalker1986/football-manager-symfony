<?php

declare(strict_types=1);

namespace App\Manager\Module\Notification\MessageHandler;

use App\Manager\Module\Notification\Message\SendPasswordResetEmail;
use App\Repository\Interface\User\UserFinderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class SendPasswordResetEmailHandler
{
    public function __construct(
        private UserFinderInterface $userFinder,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(SendPasswordResetEmail $message): void
    {
        $this->logger->info('Sending password reset email to user');
        $user = $this->userFinder->byUuid($message->userUuid);

        if (null === $user) {
            $this->logger->info('User by UUID not found, skipping password reset email');
            return;
        }

        $url = $this->urlGenerator->generate('app_reset_password', [
            'selector' => $message->selector,
            'verifier' => $message->verifier,
            '_locale' => $user->getLocale(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from('no-reply@football-manager.local')
            ->to($user->getEmailAddress())
            ->subject($this->translator->trans('email.reset_password.subject', [], 'messages', $user->getLocale()))
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'url' => $url,
                'locale' => $user->getLocale(),
                'expires_at' => $message->expiresAtInMinutes,
            ]);

        $this->mailer->send($email);
        $this->logger->info(sprintf('Reset password email sent to %s', $user->getEmailAddress()));
    }
}
