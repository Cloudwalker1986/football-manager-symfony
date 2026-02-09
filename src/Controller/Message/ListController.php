<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Entity\User;
use App\Manager\Module\Message\Enum\State;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ListController extends AbstractController
{
    #[Route('/messages', name: 'app_message_list', methods: ['GET'])]
    public function __invoke(Request $request, MessageRepository $messageRepository, string $_locale): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $manager = $user->getManager();

        if (null === $manager) {
            throw $this->createAccessDeniedException('Authenticated user is not a manager.');
        }

        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 0);
        $stateFilter = $request->query->get('state');
        $state = null;

        if ($stateFilter) {
            $state = State::tryFrom($stateFilter);
        }

        $messages = $messageRepository->findByManagerPaginated($manager, $limit, $offset, $state);
        $totalMessages = $messageRepository->countByManager($manager, $state);

        return $this->render('message/list.html.twig', [
            'messages' => $messages,
            'limit' => $limit,
            'offset' => $offset,
            'total' => $totalMessages,
            'pages' => $limit > 0 ? (int) ceil($totalMessages / $limit) : 0,
            'current_page' => $limit > 0 ? (int) floor($offset / $limit) + 1 : 1,
            'current_state' => $stateFilter,
        ]);
    }
}
