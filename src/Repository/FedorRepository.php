<?php

namespace App\Repository;

use App\Entity\Fedor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fedor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fedor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fedor[]    findAll()
 * @method Fedor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FedorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fedor::class);
    }

    // /**
    //  * @return Fedor[] Returns an array of Fedor objects
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
    public function findOneBySomeField($value): ?Fedor
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
