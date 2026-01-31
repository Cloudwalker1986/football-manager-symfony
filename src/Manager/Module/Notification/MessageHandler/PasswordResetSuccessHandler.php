<?php

declare(strict_types=1);

namespace App\Manager\Module\Notification\MessageHandler;

use App\Manager\Module\Notification\Message\PasswordResetSuccess;
use App\Repository\Interface\User\UserFinderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
readonly class PasswordResetSuccessHandler
{
    public function __construct(
        private UserFinderInterface $userFinder,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(PasswordResetSuccess $message): void
    {
        $this->logger->info('Sending password reset success email to user');
        $user = $this->userFinder->byUuid($message->userUuid);

        if (null === $user) {
            $this->logger->info('User by UUID not found, skipping password reset success email');
            return;
        }

        $email = (new TemplatedEmail())
            ->to($user->getEmailAddress())
            ->from('no-reply@localhost')
            ->subject($this->translator->trans('email.password_reset_success.subject', [], 'messages', $user->getLocale()))
            ->htmlTemplate('emails/password_reset_success.html.twig')
            ->context([
                'manager_name' => $user->getManager()?->getName(),
                'locale' => $user->getLocale(),
            ]);

        $this->mailer->send($email);
    }
}
