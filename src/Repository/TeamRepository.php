<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository implements CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function persist(object $entity): static
    {
        if (!$entity instanceof Team) {
            throw new InvalidEntityArgumentTypeException(
                Team::class,
                get_class($entity)
            );
        }

        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function flush(): static
    {
        $this->getEntityManager()->flush();

        return $this;
    }
}
