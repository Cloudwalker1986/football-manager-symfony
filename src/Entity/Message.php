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
    private ?Manager $sender;

    #[Column(enumType: State::class, options: ['default' => State::UNREAD->value])]
    private State $state = State::UNREAD;
}
