<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\BlogPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BlogPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogPage[]    findAll()
 * @method BlogPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPage::class);
    }

    public function getWithLngs($languages) {
        $qb =  $this->createQueryBuilder('t')
            ;
        foreach ($languages as $language) {
            $qb->leftJoin('t.lngs', $language, "WITH", "{$language}.lng='{$language}'")
                ->addSelect($language);
        }
        return $qb->getQuery()->getResult();
    }
}
