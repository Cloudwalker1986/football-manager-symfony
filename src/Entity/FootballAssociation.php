<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\DateTimeStamperInterface;
use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\FootballAssociationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FootballAssociationRepository::class)]
#[ORM\Table(name: 'football_association')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class FootballAssociation implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 2, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\OneToMany(targetEntity: League::class, mappedBy: 'association', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $leagues;

    public function __construct()
    {
        $this->leagues = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return Collection<int, League>
     */
    public function getLeagues(): Collection
    {
        return $this->leagues;
    }

    public function addLeague(League $league): static
    {
        if (!$this->leagues->contains($league)) {
            $this->leagues->add($league);
            $league->setAssociation($this);
        }

        return $this;
    }

    public function removeLeague(League $league): static
    {
        if ($this->leagues->removeElement($league)) {
            // set the owning side to null (unless already changed)
            if ($league->getAssociation() === $this) {
                $league->setAssociation(null);
            }
        }

        return $this;
    }
}
