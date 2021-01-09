<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }


    /*Recupere les images d'un utilisateur */

    public function findByUserIdInvoice($id)
    {

        $qb = $this->createQueryBuilder('i');

        $qb->leftJoin('i.user', 'u')
            ->andWhere('u.id like :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getResult();
    }

}
