<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\BaseController;
use App\Manager\Module\Message\Enum\State;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UnreadController extends BaseController
{
    #[Route('/messages/{uuid}/unread', name: 'app_message_unread', methods: ['POST'])]
    public function markAsUnread(
        string $uuid,
        Request $request,
        MessageRepository $messageRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        $csrfTokenValue = $request->headers->get('X-CSRF-TOKEN');

        if (null === $csrfTokenValue || !$csrfTokenManager->isTokenValid(new CsrfToken('app_message_unread', $csrfTokenValue))) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $manager = $this->getManager();

        if ($manager instanceof JsonResponse) {
            return $manager;
        }

        $message = $messageRepository->findOneBy(['uuid' => $uuid]);

        if (null === $message) {
            return new JsonResponse(['error' => 'Message not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($message->getManager()->getId() !== $manager->getId()) {
            return new JsonResponse(['error' => 'You are not authorized to modify this message.'], Response::HTTP_FORBIDDEN);
        }

        if ($message->getState()->isRead()) {
            $message->setState(State::UNREAD);
            $messageRepository->persist($message)->flush();
        }

        return new JsonResponse([
            'uuid' => $message->getUuid(),
            'state' => $message->getState()->value,
        ]);
    }
}
