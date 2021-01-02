<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    public function findByUserId($id)
    {

        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.user', 's')
            ->andWhere('s.id like :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getResult();
    }


    public function findSearch(SearchData $search) : array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.category', 'c');

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.title LIKE :q')
                ->setParameter('q', "%{$search->q}%");

        }

        return $query->getQuery()->getResult();
    }


}
