<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\IdentifierInterface;
use App\Entity\Interface\TimeStamperInterface;
use App\Entity\Trait\Identifier;
use App\Entity\Trait\DateTimeStamper;
use App\Manager\Module\User\Enum\Status;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(name: 'unique_email_address', fields: ['emailAddress'])]
#[UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[HasLifecycleCallbacks]
#[Entity(UserRepository::class)]
class User implements IdentifierInterface, TimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $emailAddress = null;

    #[Column(type: Types::STRING, length: 255)]
    private ?string $password = null;

    #[OneToOne(targetEntity: Manager::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Manager $manager = null;

    #[OneToOne(targetEntity: UserVerification::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserVerification $verification = null;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private ?string $status = null;

    #[Column(type: Types::STRING, length: 4, nullable: false, options: ['default' => 'de'])]
    private string $locale = 'de';

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(?Manager $manager): static
    {
        $manager->setUser($this);
        $this->manager = $manager;

        return $this;
    }

    public function getVerification(): ?UserVerification
    {
        return $this->verification;
    }

    public function setVerification(?UserVerification $verification): static
    {
        $verification->setUser($this);
        $this->verification = $verification;

        return $this;
    }

    public function getStatus(): Status
    {
        return Status::tryFrom($this->status);
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status->value;

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
}
