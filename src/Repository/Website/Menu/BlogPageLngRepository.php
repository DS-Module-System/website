<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\BlogPageLng;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BlogPageLng|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogPageLng|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogPageLng[]    findAll()
 * @method BlogPageLng[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogPageLngRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPageLng::class);
    }

    // /**
    //  * @return BlogPageLng[] Returns an array of BlogPageLng objects
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
    public function findOneBySomeField($value): ?BlogPageLng
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
