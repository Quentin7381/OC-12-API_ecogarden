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
        $advices = $this->createQueryBuilder('a')
            ->getQuery()
            ->getResult();

        return array_filter($advices, function($advice) use ($month) {
            return in_array($month, $advice->getMonth());
        });
    }
}
