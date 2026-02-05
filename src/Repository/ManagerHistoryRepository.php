<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ManagerHistory;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class
ManagerHistoryRepository extends ServiceEntityRepository implements CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManagerHistory::class);
    }

    /**
     * Optional explicit save method
     */
    public function persist(object $entity): static
    {
        if (!$entity instanceof ManagerHistory) {
            throw new InvalidEntityArgumentTypeException(
                ManagerHistory::class,
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
