<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StadiumBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StadiumBlock>
 */
class StadiumBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StadiumBlock::class);
    }
}
