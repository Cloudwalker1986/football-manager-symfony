<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\IdentifierInterface;
use App\Entity\Interface\TimeStamperInterface;
use App\Entity\Trait\Identifier;
use App\Entity\Trait\DateTimeStamper;
use App\Repository\UserVerificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[HasLifecycleCallbacks]
#[Entity(UserVerificationRepository::class)]
class UserVerification implements IdentifierInterface, TimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[OneToOne(targetEntity: User::class, inversedBy: 'verification')]
    private ?User $user = null;

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
