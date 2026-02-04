<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Command\UpdateProfile;

use App\Manager\Framework\Command\Interface\CommandInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class UpdateProfileCommand implements CommandInterface
{
    #[NotBlank(message: 'profile.email.not_blank')]
    #[Email(message: 'profile.email.invalid')]
    private string $email = '';

    #[NotBlank(message: 'profile.manager_name.not_blank')]
    private string $managerName = '';

    private string $locale = 'de';

    private ?string $currentPassword = null;

    private ?string $newPassword = null;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getCurrentPassword(): ?string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(?string $currentPassword): static
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}
