<?php

namespace App\Repository;

use App\Entity\Pieces;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pieces|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pieces|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pieces[]    findAll()
 * @method Pieces[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PiecesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pieces::class);
    }

    // /**
    //  * @return Pieces[] Returns an array of Pieces objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pieces
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
