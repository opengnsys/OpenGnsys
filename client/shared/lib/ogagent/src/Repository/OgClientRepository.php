<?php

namespace App\Repository;

use App\Entity\OgClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OgClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method OgClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method OgClient[]    findAll()
 * @method OgClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OgClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OgClient::class);
    }

    // /**
    //  * @return OgClient[] Returns an array of OgClient objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OgClient
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
