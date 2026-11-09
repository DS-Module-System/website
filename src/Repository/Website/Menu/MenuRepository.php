<?php

namespace App\Repository\Website\Menu;

use App\Entity\Website\Menu\Menu;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends NestedTreeRepository
{

    public function getMenuWithLng($id, $lng = null, $language = null) {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.lngs', 'tl')
            ->addSelect('tl')
            ->andWhere('t.id = :id')
            ->setParameter('id', $id);

        if ($language !== null) {
            $qb->orderBy("FIELD(tl.lng, '" . implode("','", $language) . "')");
        }

        if ($lng !== null) {
            $qb->andWhere('tl.lng = :lng')->setParameter('lng', $lng);
        }
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getMenuWithLngByRouteName(string $routeName, $lng = null, $language = null) {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.lngs', 'tl')
            ->addSelect('tl')
            ->andWhere('t.routeName = :routeName')
            ->setParameter('routeName', $routeName);

        if ($language !== null) {
            $qb->orderBy("FIELD(tl.lng, '" . implode("','", $language) . "')");
        }

        if ($lng !== null) {
            $qb->andWhere('tl.lng = :lng')->setParameter('lng', $lng);
        }
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getMenuWithLngOnlyAndIsActive($locale){
        return $this->createQueryBuilder('m')
            ->leftJoin('m.lngs', 'ml')
            ->addSelect('ml')
            ->where('ml.lng = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('ml.is_active = :active')
            ->setParameter('active', 1)
            ->addOrderBy('m.lvl', 'ASC')
            ->addOrderBy('m.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMenuAll($lng = null){

        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.lngs', 'ml')
            ->addSelect('ml')
            ->addOrderBy('m.lft', 'ASC')
        ;

        if($lng !== null) {
            $qb->andWhere('ml.lng = :lng')->setParameter('lng', $lng);
        }

        // TODO trqbva li da se keshira ?

//        return $qb->getQuery()->enableResultCache(2);
        return $qb->getQuery();
    }

    public function getMetaTagsWithCurrentLng($locale, $routeName = null){
        $routeName = (is_null($routeName)) ? '' : $routeName;
        return $this->createQueryBuilder('m')
            ->leftJoin('m.lngs', 'ml')
            ->addSelect('ml')
            ->where('ml.lng = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('m.route_name = :routeName')
            ->setParameter(':routeName', $routeName)
            ->getQuery()
            ->getResult();
    }
}
