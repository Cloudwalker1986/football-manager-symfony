<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\User\UserFinderInterface;
use App\Repository\Interface\User\UniqueEmailInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class UserRepository
    extends ServiceEntityRepository
    implements UniqueEmailInterface, CreateEntityInterface, UserFinderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function byUuid(Uuid $uuid): ?User
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    public function isEmailAddressUnique(string $email): bool
    {
        return $this->createQueryBuilder('u')
            ->select('1')
            ->where('u.emailAddress = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() === null;
    }

    /**
     * Optional explicit save method
     */
    public function persist(object $entity): static
    {
        if (!$entity instanceof User) {
            throw new InvalidEntityArgumentTypeException(
                User::class,
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
