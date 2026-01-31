<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Uid\Uuid;

trait IdentifierTrait
{
    #[Column(type: Types::INTEGER)]
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Column(type: Types::GUID)]
    private ?string $uuid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return is_string($this->uuid) ? Uuid::fromString($this->uuid) : null;
    }

    #[PrePersist]
    public function onPrePersistForUuid(): static
    {
        $this->uuid = $this->uuid ?? Uuid::v7()->toString();

        return $this;
    }
}
