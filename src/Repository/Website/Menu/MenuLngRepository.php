<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\MenuLng;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuLng|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuLng|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuLng[]    findAll()
 * @method MenuLng[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuLngRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuLng::class);
    }

    // /**
    //  * @return MenuLng[] Returns an array of MenuLng objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MenuLng
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
