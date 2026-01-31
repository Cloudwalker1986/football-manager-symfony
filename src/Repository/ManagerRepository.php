<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Manager;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\Manager\UniqueManagerNameInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ManagerRepository extends ServiceEntityRepository implements UniqueManagerNameInterface, CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manager::class);
    }

    public function isManagerNameUnique(string $name): bool
    {
        return $this->createQueryBuilder('m')
            ->select('1')
            ->where('m.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() === null;
    }

    /**
     * Optional explicit save method
     */
    public function persist(object $entity): static
    {
        if (!$entity instanceof Manager) {
            throw new InvalidEntityArgumentTypeException(
                Manager::class,
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
