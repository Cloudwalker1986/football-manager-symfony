<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\IdentifierInterface;
use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Trait\Identifier;
use App\Entity\Trait\DateTimeStamper;
use App\Repository\ManagerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
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

    #[ORM\OneToOne(targetEntity: Club::class, mappedBy: 'manager')]
    private ?Club $club = null;

    #[OneToMany(targetEntity: ManagerHistory::class, mappedBy: 'manager')]
    #[OrderBy(['createdAt' => 'DESC'])]
    private Collection $history;

    public function __construct()
    {
        $this->history = new ArrayCollection();
    }

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

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }
}
