<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\BaseController;
use App\Repository\Interface\Message\UnreadMessageCountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UnreadCountController extends BaseController
{
    #[Route('/messages/unread-count', name: 'app_message_unread_count', methods: ['GET'])]
    public function getUnreadCount(UnreadMessageCountInterface $messageRepository): JsonResponse
    {
        $manager = $this->getManager();

        if ($manager instanceof JsonResponse) {
            return $manager;
        }

        $unreadCount = $messageRepository->getUnreadMessageCount($manager);

        return new JsonResponse(['unreadCount' => $unreadCount]);
    }
}
