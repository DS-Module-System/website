<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\ContactPageLng;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactPageLng|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactPageLng|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactPageLng[]    findAll()
 * @method ContactPageLng[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactPageLngRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactPageLng::class);
    }
}
