<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\BaseController;
use App\Manager\Framework\Command\CommandBus;
use App\Manager\Module\Message\Command\DeleteMessage\DeleteMessageCommand;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DeleteController extends BaseController
{
    #[Route('/messages/{uuid}/delete', name: 'app_message_delete', methods: ['POST'])]
    public function delete(
        string $uuid,
        Request $request,
        MessageRepository $messageRepository,
        CommandBus $commandBus
    ): Response
    {
        $manager = $this->getManager();

        if ($manager instanceof JsonResponse) {
            return $manager;
        }

        $message = $messageRepository->findOneBy(['uuid' => $uuid]);

        if (null === $message) {
            return new JsonResponse(['error' => 'Message not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($message->getManager()->getId() !== $manager->getId()) {
            return new JsonResponse(['error' => 'You are not authorized to delete this message.'], Response::HTTP_FORBIDDEN);
        }

        $commandBus->handle(new DeleteMessageCommand($uuid));

        return new JsonResponse(['success' => true]);
    }
}
