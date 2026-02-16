<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Manager;
use App\Entity\Message;
use App\Manager\Module\Message\Enum\State;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\DeleteEntityInterface;
use App\Repository\Interface\Message\MessageFinderInterface;
use App\Repository\Interface\Message\UnreadMessageCountInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class MessageRepository extends ServiceEntityRepository implements
    CreateEntityInterface,
    UnreadMessageCountInterface,
    DeleteEntityInterface,
    MessageFinderInterface
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

    public function delete(object $entity): static
    {
        if (!$entity instanceof Message) {
            throw new InvalidEntityArgumentTypeException(
                Message::class,
                get_class($entity)
            );
        }

        $this->getEntityManager()->remove($entity);

        return $this;
    }

    /**
     * @return Message[]
     */
    public function findByManagerPaginated(
        Manager $manager,
        int $limit = 10,
        int $offset = 0,
        ?State $state = null
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

    public function byUuid(Uuid $uuid): ?Message
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    /**
     * @return Message[]
     */
    public function findOlderThan(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
}
