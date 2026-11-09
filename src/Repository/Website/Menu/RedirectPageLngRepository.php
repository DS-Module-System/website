<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\RedirectPageLng;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedirectPageLng|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedirectPageLng|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedirectPageLng[]    findAll()
 * @method RedirectPageLng[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RedirectPageLngRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedirectPageLng::class);
    }

    // /**
    //  * @return RedirectPageLng[] Returns an array of RedirectPageLng objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RedirectPageLng
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
