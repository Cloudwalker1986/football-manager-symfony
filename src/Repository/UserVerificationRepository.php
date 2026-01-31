<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserVerification;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserVerificationRepository extends ServiceEntityRepository implements CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVerification::class);
    }

    public function persist(object $entity): static
    {
        if (!$entity instanceof UserVerification) {
            throw new InvalidEntityArgumentTypeException(
                UserVerification::class,
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
