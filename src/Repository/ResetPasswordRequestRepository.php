<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\User\ResetPasswordInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResetPasswordRequestRepository
    extends ServiceEntityRepository
    implements ResetPasswordInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function persist(object $entity): static
    {

        if (!$entity instanceof ResetPasswordRequest) {
            throw new InvalidEntityArgumentTypeException(
                ResetPasswordRequest::class,
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

    public function remove(ResetPasswordRequest $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
    }

    public function removeUserToken(User $user): void
    {
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
