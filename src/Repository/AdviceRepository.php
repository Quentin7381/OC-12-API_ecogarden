<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    public function findByMonth($month)
    {
        // Find if the month is in the advice month array
        return $this->createQueryBuilder('a')
            ->andWhere('a.month LIKE :month')
            ->setParameter('month', '%' . $month . '%')
            ->getQuery()
            ->getResult();
    }
}
