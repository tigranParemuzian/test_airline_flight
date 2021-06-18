<?php

namespace App\Repository;

use App\Entity\FlightOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FlightOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlightOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlightOrder[]    findAll()
 * @method FlightOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlightOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlightOrder::class);
    }

    // /**
    //  * @return FlightOrder[] Returns an array of FlightOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FlightOrder
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
