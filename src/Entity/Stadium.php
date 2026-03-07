<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\StadiumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StadiumRepository::class)]
#[ORM\Table(name: 'stadium')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\UniqueConstraint(name: 'unique_club_id', fields: ['club'])]
#[ORM\HasLifecycleCallbacks]
class Stadium implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(targetEntity: Club::class, inversedBy: 'stadium')]
    #[ORM\JoinColumn(name: 'club_id', referencedColumnName: 'id', nullable: false)]
    private ?Club $club = null;

    #[ORM\OneToMany(targetEntity: StadiumBlock::class, mappedBy: 'stadium', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $blocks;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCapacity(): int
    {
        $totalCapacity = 0;
        foreach ($this->blocks as $block) {
            $totalCapacity += $block->getCapacity();
        }

        return $totalCapacity;
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

    /**
     * @return Collection<int, StadiumBlock>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(StadiumBlock $block): static
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->setStadium($this);
        }

        return $this;
    }

    public function removeBlock(StadiumBlock $block): static
    {
        if ($this->blocks->removeElement($block)) {
            // set the owning side to null (unless already changed)
            if ($block->getStadium() === $this) {
                $block->setStadium(null);
            }
        }

        return $this;
    }
}
