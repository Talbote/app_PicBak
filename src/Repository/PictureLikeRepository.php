<?php

namespace App\Repository;

use App\Entity\PictureLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PictureLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method PictureLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method PictureLike[]    findAll()
 * @method PictureLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PictureLike::class);
    }

    // /**
    //  * @return PictureLike[] Returns an array of PictureLike objects
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
    public function findOneBySomeField($value): ?PictureLike
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
