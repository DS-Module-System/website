<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\ContactPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactPage[]    findAll()
 * @method ContactPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactPage::class);
    }
}
