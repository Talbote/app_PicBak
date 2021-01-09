<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Category;
use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;


/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $pagination)
    {
        parent::__construct($registry, Picture::class);
        $this->pagination = $pagination;
    }

    /*Recupere les images d'un utilisateur */

    public function findByUserIdPicture($id)
    {

        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.user', 's')
            ->andWhere('s.id like :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupere les images avec une recherche
     * @return PaginationInterface
     *
     */

    public function findSearch(SearchData $search): PaginationInterface
    {
        /* Jointure entre les pictures et categories*/
        $query = $this
            ->createQueryBuilder('p');


        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.title LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }



        if (!empty($search->categories)) {
            $query = $query
                ->select('c', 'p')
                ->join('p.category', 'c')
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);

        }

        $query = $query->getQuery();

        return $this->pagination->paginate(
            $query,
            1,
            15
        );

    }


}


