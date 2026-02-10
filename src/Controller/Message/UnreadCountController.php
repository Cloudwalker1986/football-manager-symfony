<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Entity\User;
use App\Manager\Module\Message\Enum\State;
use App\Repository\Interface\Message\UnreadMessageCountInterface;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UnreadCountController extends AbstractController
{
    #[Route('/messages/unread-count', name: 'app_message_unread_count', methods: ['GET'])]
    public function getUnreadCount(UnreadMessageCountInterface $messageRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $manager = $user->getManager();

        if (null === $manager) {
            return new JsonResponse(['error' => 'Authenticated user is not a manager.'], Response::HTTP_FORBIDDEN);
        }

        $unreadCount = $messageRepository->getUnreadMessageCount($manager);

        return new JsonResponse(['unreadCount' => $unreadCount]);
    }
}
