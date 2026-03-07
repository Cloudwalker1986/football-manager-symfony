<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\IdentifierInterface;
use App\Entity\Trait\Identifier;
use App\Repository\StadiumBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StadiumBlockRepository::class)]
#[ORM\Table(name: 'stadium_block')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class StadiumBlock implements IdentifierInterface
{
    use Identifier;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Stadium::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stadium $stadium = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $standSeatCapacity = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $standSeatPriceCategory = 1;

    #[ORM\Column(type: Types::INTEGER)]
    private int $standSeatReservedSpace = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sitSeatCapacity = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sitSeatPriceCategory = 1;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sitSeatReservedSpace = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $vipLogeCapacity = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $vipLogePriceCategory = 1;

    #[ORM\Column(type: Types::INTEGER)]
    private int $vipLogeReservedSpace = 0;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStadium(): ?Stadium
    {
        return $this->stadium;
    }

    public function setStadium(?Stadium $stadium): static
    {
        $this->stadium = $stadium;

        return $this;
    }

    public function getStandSeatCapacity(): int
    {
        return $this->standSeatCapacity;
    }

    public function setStandSeatCapacity(int $standSeatCapacity): static
    {
        $this->standSeatCapacity = $standSeatCapacity;

        return $this;
    }

    public function getStandSeatPriceCategory(): int
    {
        return $this->standSeatPriceCategory;
    }

    public function setStandSeatPriceCategory(int $standSeatPriceCategory): static
    {
        $this->standSeatPriceCategory = $standSeatPriceCategory;

        return $this;
    }

    public function getStandSeatReservedSpace(): int
    {
        return $this->standSeatReservedSpace;
    }

    public function setStandSeatReservedSpace(int $standSeatReservedSpace): static
    {
        $this->standSeatReservedSpace = $standSeatReservedSpace;

        return $this;
    }

    public function getSitSeatCapacity(): int
    {
        return $this->sitSeatCapacity;
    }

    public function setSitSeatCapacity(int $sitSeatCapacity): static
    {
        $this->sitSeatCapacity = $sitSeatCapacity;

        return $this;
    }

    public function getSitSeatPriceCategory(): int
    {
        return $this->sitSeatPriceCategory;
    }

    public function setSitSeatPriceCategory(int $sitSeatPriceCategory): static
    {
        $this->sitSeatPriceCategory = $sitSeatPriceCategory;

        return $this;
    }

    public function getSitSeatReservedSpace(): int
    {
        return $this->sitSeatReservedSpace;
    }

    public function setSitSeatReservedSpace(int $sitSeatReservedSpace): static
    {
        $this->sitSeatReservedSpace = $sitSeatReservedSpace;

        return $this;
    }

    public function getVipLogeCapacity(): int
    {
        return $this->vipLogeCapacity;
    }

    public function setVipLogeCapacity(int $vipLogeCapacity): static
    {
        $this->vipLogeCapacity = $vipLogeCapacity;

        return $this;
    }

    public function getVipLogePriceCategory(): int
    {
        return $this->vipLogePriceCategory;
    }

    public function setVipLogePriceCategory(int $vipLogePriceCategory): static
    {
        $this->vipLogePriceCategory = $vipLogePriceCategory;

        return $this;
    }

    public function getVipLogeReservedSpace(): int
    {
        return $this->vipLogeReservedSpace;
    }

    public function setVipLogeReservedSpace(int $vipLogeReservedSpace): static
    {
        $this->vipLogeReservedSpace = $vipLogeReservedSpace;

        return $this;
    }

    public function getCapacity(): int
    {
        return $this->standSeatCapacity + $this->sitSeatCapacity + $this->vipLogeCapacity;
    }
}
