<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Manager;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseController extends AbstractController
{
    public function getAuthenticatedUser(): JsonResponse|UserInterface
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Authenticated user is invalid.'], Response::HTTP_UNAUTHORIZED);
        }

        return $user;
    }

    protected function getManager(): JsonResponse|Manager
    {
        $user = $this->getAuthenticatedUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Authenticated user is invalid.'], Response::HTTP_UNAUTHORIZED);
        }

        $manager = $user->getManager();

        if (null === $manager) {
            return new JsonResponse(['error' => 'Authenticated user is not a manager.'], Response::HTTP_FORBIDDEN);
        }

        return $manager;
    }
}
