<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\StaticPageLng;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StaticPageLng|null find($id, $lockMode = null, $lockVersion = null)
 * @method StaticPageLng|null findOneBy(array $criteria, array $orderBy = null)
 * @method StaticPageLng[]    findAll()
 * @method StaticPageLng[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StaticPageLngRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaticPageLng::class);
    }

    // /**
    //  * @return StaticPageLng[] Returns an array of StaticPageLng objects
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
    public function findOneBySomeField($value): ?StaticPageLng
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
