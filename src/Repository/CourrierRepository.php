<?php

namespace App\Repository;

use App\Entity\Courrier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Courrier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Courrier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Courrier[]    findAll()
 * @method Courrier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Courrier::class);
    }

    // /**
    //  * @return Courrier[] Returns an array of Courrier objects
    //  */
    
    public function findByExampleField($nom_recipient)
    {
       /* return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;*/
            $qb = $this->createQueryBuilder('c')
            ->where('c.recipient != :recipient')
            ->setParameter('recipient', $nom_recipient)
            ->orderBy('c.recipient', 'ASC');
        $query = $qb->getQuery();

        return $query->getResult();
    }
    



    /*
    public function findOneBySomeField($value): ?Courrier
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
