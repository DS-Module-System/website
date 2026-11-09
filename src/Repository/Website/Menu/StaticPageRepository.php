<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\StaticPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StaticPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method StaticPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method StaticPage[]    findAll()
 * @method StaticPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StaticPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaticPage::class);
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
