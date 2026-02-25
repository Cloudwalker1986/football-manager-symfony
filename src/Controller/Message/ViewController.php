<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Entity\User;
use App\Manager\Module\Message\Enum\State;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
class ViewController extends AbstractController
{
    #[Route('/messages/{uuid}/view', name: 'app_message_view', methods: ['GET'])]
    public function getMessage(string $uuid, MessageRepository $messageRepository, TranslatorInterface $translator): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $manager = $user->getManager();

        if (null === $manager) {
            return new JsonResponse(['error' => 'Authenticated user is not a manager.'], Response::HTTP_FORBIDDEN);
        }

        $message = $messageRepository->findOneBy(['uuid' => $uuid]);

        if (null === $message) {
            return new JsonResponse(['error' => 'Message not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($message->getManager()->getId() !== $manager->getId()) {
            return new JsonResponse(['error' => 'You are not authorized to view this message.'], Response::HTTP_FORBIDDEN);
        }

        if ($message->getState()->isUnread()) {
            $message->setState(State::READ);
            $messageRepository->persist($message)->flush();
        }

        $subject = $message->getSubject();
        $body = $message->getMessage();
        $parameters = $message->getParameters() ?? [];

        // Try to translate. If it's a key, it will be translated. If not, the original will be returned.
        $translatedSubject = $translator->trans($subject ?? '', [], 'messages');

        // For the body, we also try to translate. If it's the club creation message key, we want the title + body.
        $translatedBody = $translator->trans($body ?? '', $parameters, 'messages');

        // Specialized handling for club creation message to match previous logic (title + body)
        if ($body === 'wizard.club.creation.message.body') {
            $title = $translator->trans('wizard.club.creation.message.title', [], 'messages');
            $translatedBody = sprintf("<strong>%s</strong><br><br>%s", $title, $translatedBody);
        }

        return new JsonResponse([
            'uuid' => $message->getUuid(),
            'subject' => $translatedSubject,
            'message' => $translatedBody,
            'sender' => $message->getSender() ? $message->getSender()->getName() : ($message->getParameters() ? $translator->trans('message.list.table.sender_name.default') : null),
            'createdAt' => $message->getCreatedAt() ? $message->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'state' => $message->getState()->value,
        ]);
    }
}
