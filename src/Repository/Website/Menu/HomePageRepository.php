<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\HomePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HomePage|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomePage|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomePage[]    findAll()
 * @method HomePage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomePageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomePage::class);
    }

    public function getWithLngs($languages)
    {
        $qb =  $this->createQueryBuilder('t');
        foreach ($languages as $language) {
            $qb->leftJoin('t.lngs', $language, "WITH", "{$language}.lng='{$language}'")
                ->addSelect($language);
        }
        return $qb->getQuery()->getResult();
    }
}
