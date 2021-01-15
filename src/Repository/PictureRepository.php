<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Category;
use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\VarDumper\Caster\PgSqlCaster;


/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Picture::class);
        $this->paginator = $paginator;
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

public function findSearch(SearchData $search, $request): PaginationInterface
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

        return $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            9 /*limit per page*/
        );
    }


}


