<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FootballAssociation;
use App\Repository\Exception\InvalidEntityArgumentTypeException;
use App\Repository\Interface\CreateEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FootballAssociation>
 *
 * @method FootballAssociation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FootballAssociation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FootballAssociation[]    findAll()
 * @method FootballAssociation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FootballAssociationRepository extends ServiceEntityRepository implements CreateEntityInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FootballAssociation::class);
    }

    public function persist(object $entity): static
    {
        if (!$entity instanceof FootballAssociation) {
            throw new InvalidEntityArgumentTypeException(
                FootballAssociation::class,
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
