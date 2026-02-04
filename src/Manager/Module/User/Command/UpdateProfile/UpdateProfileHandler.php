<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Command\UpdateProfile;

use App\Entity\User;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class UpdateProfileHandler implements CommandHandlerInterface
{
    public function __construct(
        #[Target(UserRepository::class)]
        private CreateEntityInterface $userRepository,
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof UpdateProfileCommand;
    }

    public function handle(CommandInterface|UpdateProfileCommand $command): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        if ($command->getNewPassword()) {
            if (!$command->getCurrentPassword() || !$this->passwordHasher->isPasswordValid($user, $command->getCurrentPassword())) {
                throw new BadRequestHttpException('profile.current_password.invalid');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $command->getNewPassword()));
        }

        $user->setEmailAddress($command->getEmail());
        $user->setLocale($command->getLocale());

        $manager = $user->getManager();
        if ($manager) {
            $manager->setName($command->getManagerName());
        }

        $this->userRepository->flush();
    }
}
