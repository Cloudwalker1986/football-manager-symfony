<?php

declare(strict_types=1);

namespace App\Entity;


use App\Entity\Trait\DateTimeStamper;
use App\Entity\Trait\Identifier;
use App\Manager\Module\Message\Enum\State;
use App\Repository\ManagerHistoryRepository;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[HasLifecycleCallbacks]
#[Entity(repositoryClass: MessageRepository::class)]
class Message
{
    use Identifier;
    use DateTimeStamper;

    #[Column(type: Types::TEXT, nullable: false)]
    private ?string $message = null;

    #[ManyToOne(targetEntity: Manager::class)]
    #[JoinColumn(nullable: false)]
    private Manager $manager;

    #[ManyToOne(targetEntity: Manager::class)]
    private ?Manager $sender = null;

    #[Column(enumType: State::class, options: ['default' => State::UNREAD->value])]
    private State $state = State::UNREAD;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $subject = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function setManager(Manager $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getSender(): ?Manager
    {
        return $this->sender;
    }

    public function setSender(?Manager $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }
}
