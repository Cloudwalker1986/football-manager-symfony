<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Command\UserRegistration;

use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\User\Constraint\UniqueEmail;
use App\Manager\Module\User\Constraint\UniqueManagerName;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserRegisterCommand implements CommandInterface
{
    #[NotBlank(message: 'registration.email.not_blank')]
    #[UniqueEmail]
    private string $email = '';

    #[NotBlank(message: 'registration.password.not_blank')]
    #[Length(min: 8, minMessage: 'registration.password.min_length')]
    private string $password = '';

    #[NotBlank(message: 'registration.manager_name.not_blank')]
    #[UniqueManagerName]
    private string $managerName = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getManagerName(): string
    {
        return $this->managerName;
    }

    public function setManagerName(string $managerName): static
    {
        $this->managerName = $managerName;

        return $this;
    }
}
