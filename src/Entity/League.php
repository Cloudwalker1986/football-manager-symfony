<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\LeagueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ORM\Table(name: 'league')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\Index(name: 'idx_league_association_id', fields: ['association'])]
#[ORM\Index(name: 'idx_league_association_level', fields: ['association', 'level'])]
#[ORM\HasLifecycleCallbacks]
class League implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $level = null;

    #[ORM\ManyToOne(targetEntity: FootballAssociation::class, inversedBy: 'leagues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FootballAssociation $association = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getAssociation(): ?FootballAssociation
    {
        return $this->association;
    }

    public function setAssociation(?FootballAssociation $association): static
    {
        $this->association = $association;

        return $this;
    }
}
