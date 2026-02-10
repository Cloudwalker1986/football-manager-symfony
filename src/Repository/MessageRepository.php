<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Manager;
use App\Entity\Message;
use App\Manager\Module\Message\Enum\State;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\Message\UnreadMessageCountInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository implements
    CreateEntityInterface,
    UnreadMessageCountInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Optional explicit save method
     */
    public function persist(object $entity): static
    {
        if (!$entity instanceof Message) {
            throw new InvalidEntityArgumentTypeException(
                Message::class,
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

    /**
     * @return Message[]
     */
    public function findByManagerPaginated(
        \App\Entity\Manager $manager,
        int $limit = 10,
        int $offset = 0,
        ?\App\Manager\Module\Message\Enum\State $state = null
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.manager = :manager')
            ->setParameter('manager', $manager)
            ->orderBy('m.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($state !== null) {
            $qb->andWhere('m.state = :state')
                ->setParameter('state', $state);
        }

        return $qb->getQuery()->getResult();
    }

    public function getUnreadMessageCount(Manager $manager): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.manager = :manager')
            ->andWhere('m.state = :state')
            ->setParameter('state', State::UNREAD)
            ->setParameter('manager', $manager);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
