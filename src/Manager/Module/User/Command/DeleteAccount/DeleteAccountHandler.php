<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Command\DeleteAccount;

use App\Entity\User;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class DeleteAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof DeleteAccountCommand;
    }

    public function handle(CommandInterface $command): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        // 1. Remove associated data that doesn't cascade
        $this->resetPasswordRequestRepository->removeUserToken($user);

        // 2. Remove the user (cascades to Manager and UserVerification)
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // 3. Invalidate the session and clear the token
        $this->tokenStorage->setToken(null);
        $this->requestStack->getSession()->invalidate();
    }
}
