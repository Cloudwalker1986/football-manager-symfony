<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\ManyToOne;

#[HasLifecycleCallbacks]
#[Entity(ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[Column(type: Types::STRING, length: 255)]
    private ?string $selector = null;

    #[Column(type: Types::STRING, length: 255)]
    private ?string $hashedToken = null;

    #[ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct(User $user, \DateTimeImmutable $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->expiresAt = $expiresAt;
        $this->selector = $selector;
        $this->hashedToken = $hashedToken;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function getHashedToken(): ?string
    {
        return $this->hashedToken;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= time();
    }
}
