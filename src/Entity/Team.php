<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Manager\Module\Team\Enum\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: 'team')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class Team implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\Column(type: 'string', length: 255, enumType: TeamType::class)]
    private ?TeamType $type = null;

    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $club = null;

    #[ORM\ManyToOne(targetEntity: League::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?League $league = null;

    public function getType(): ?TeamType
    {
        return $this->type;
    }

    public function setType(TeamType $type): static
    {
        $this->type = $type;

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

    public function getLeague(): ?League
    {
        return $this->league;
    }

    public function setLeague(?League $league): static
    {
        $this->league = $league;

        return $this;
    }
}
