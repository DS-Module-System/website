<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\RedirectPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedirectPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedirectPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedirectPage[]    findAll()
 * @method RedirectPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RedirectPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedirectPage::class);
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
