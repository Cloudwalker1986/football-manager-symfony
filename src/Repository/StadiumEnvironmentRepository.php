<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Club;
use App\Entity\StadiumEnvironment;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StadiumEnvironment>
 */
class StadiumEnvironmentRepository extends ServiceEntityRepository implements CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StadiumEnvironment::class);
    }

    public function persist(object $entity): static
    {
        if (!$entity instanceof StadiumEnvironment) {
            throw new InvalidEntityArgumentTypeException(
                StadiumEnvironment::class,
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
