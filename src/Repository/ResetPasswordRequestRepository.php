<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResetPasswordRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function persist(ResetPasswordRequest $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(ResetPasswordRequest $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
    }

    public function findMostRecentNonExpiredRequest(User $user): ?ResetPasswordRequest
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.expiresAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeAllForUser(User $user): void
    {
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
