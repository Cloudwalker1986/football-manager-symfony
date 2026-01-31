<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\IdentifierInterface;
use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Trait\Identifier;
use App\Entity\Trait\DateTimeStamper;
use App\Repository\ManagerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;


#[UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[HasLifecycleCallbacks]
#[Entity(repositoryClass: ManagerRepository::class)]
class Manager implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[OneToOne(targetEntity: User::class, inversedBy: 'manager')]
    #[JoinColumn(nullable: false)]
    private ?User $user = null;


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Manager
    {
        $this->user = $user;

        return $this;
    }
}
