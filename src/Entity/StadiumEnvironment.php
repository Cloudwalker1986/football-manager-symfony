<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\StadiumEnvironmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StadiumEnvironmentRepository::class)]
#[ORM\Table(name: 'stadium_environment')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\UniqueConstraint(name: 'unique_club_id', fields: ['club'])]
#[ORM\HasLifecycleCallbacks]
class StadiumEnvironment implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\OneToOne(targetEntity: Club::class, inversedBy: 'stadiumEnvironment')]
    #[ORM\JoinColumn(name: 'club_id', referencedColumnName: 'id', nullable: false)]
    private ?Club $club = null;

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
