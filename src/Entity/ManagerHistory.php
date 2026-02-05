<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Repository\ManagerHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[HasLifecycleCallbacks]
#[Entity(repositoryClass: ManagerHistoryRepository::class)]
class ManagerHistory
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\ManyToOne(targetEntity: Manager::class, inversedBy: 'history')]
    private ?Manager $manager = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $message = null;

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(?Manager $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
