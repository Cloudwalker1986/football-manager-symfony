<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\User;
use App\Repository\Interface\Message\UnreadMessageCountInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessageExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly UnreadMessageCountInterface $messageRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_message_count', [$this, 'getUnreadMessageCount']),
        ];
    }

    public function getUnreadMessageCount(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return 0;
        }

        $manager = $user->getManager();

        if (null === $manager) {
            return 0;
        }

        return $this->messageRepository->getUnreadMessageCount($manager);
    }
}
