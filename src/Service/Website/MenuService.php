<?php

namespace App\Service\Website;

use App\Entity\Website\Menu\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuService
{

    private $menuTree = null;
    private $request;
    private $activeMenuItem = null;
    private $menuSingleArr = null;

    private $menuItemsPath = [];

    private $menuArrAssoc = null;

    public function __construct(
        private EntityManagerInterface $em,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getMenuArr()
    {

        if ($this->menuArrAssoc) {
            return $this->menuArrAssoc;
        }

        $menus = $this->em->getRepository(Menu::class)->getMenuAll()->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)->getArrayResult();

        if ($menus) {
            foreach ($menus as $menu) {
                if (empty($menu['url'])) {
                    $menu['url'] = "/pageId-{$menu['id']}";
                }

                $tmpMenu = $menu;
                $tmpMenu['lngs'] = [];
                if (!empty($menu['lngs'])) {
                    foreach ($menu['lngs'] as $lngRow) {
                        $cid = $menu['id'];
                        $lng = $lngRow['lng'];
                        if (empty($lngRow['url']) && !empty($menu['url'])) {
                            $lngRow['url'] = $menu['url'];
                        }
                        $tmpMenu['lngs'][$lng] = $lngRow;
                    }
                }
                $this->menuArrAssoc[$tmpMenu['id']] = $tmpMenu;
            }
        }

        return $this->menuArrAssoc;
    }

    public function load()
    {
        if ($this->menuTree === null) {
            $repo = $this->em->getRepository(Menu::class);
            $repo->setChildrenIndex('children');
            if ($this->request) {
                $this->menuTree = $repo->buildTreeArray($repo->getMenuAll($this->request->getLocale())->getArrayResult());
            }
        }

        $menuArrAssoc = $this->getMenuArr();
        $currLng = $this->request->getLocale();

        foreach ($menuArrAssoc as &$menuItem) {

            //            if(empty($menuItem['lngs'][$currLng]['isActive'])) continue;

            $menuItem['selected'] = false;

            $menuItem['menuTitle'] = $menuItem['lngs'][$currLng]['menuTitle'];
            $menuItem['contentTitle'] = $menuItem['lngs'][$currLng]['contentTitle'];
            $menuItem['pageTitle'] = $menuItem['lngs'][$currLng]['pageTitle'];
            $menuItem['url'] = $menuItem['lngs'][$currLng]['url'];
            $menuItem['description'] = $menuItem['lngs'][$currLng]['description'];
            $menuItem['body'] = $menuItem['lngs'][$currLng]['body'];
            $menuItem['isActive'] = $menuItem['lngs'][$currLng]['isActive'];
        }
        unset($menuItem);
        $this->menuArrAssoc = $menuArrAssoc;

        $this->findActiveMenuItem();

        $this->prepareMenuArrAssoc();
    }

    public function getMenuSingleArrAssoc()
    {
        if ($this->menuSingleArr === null) {
            $this->menuSingleArr = [];
            $arr = $this->flatten($this->menuTree);
            foreach ($arr as $v) {
                $this->menuSingleArr[$v['id']] = $v;
            }
        }
        return $this->menuSingleArr;
    }

    public function flatten($arr, $parentId = 0)
    {
        $result = [];
        if ($arr) {
            foreach ($arr as $item) {
                $item['parentId'] = $parentId;
                if (isset($item['children'])) {
                    $result = array_merge($result, $this->flatten($item['children'], $item['id']));
                }
                unset($item['children']);
                $result[] = $item;
            }
        }
        return $result;
    }

    public function findActiveMenuItem(): void
    {
        if ($this->activeMenuItem === null) {
            $menuId = $this->request->get('menuId', null);
            if ($menuId !== null) {
                $menuArrAssoc = $this->getMenuArr();
                if (isset($menuArrAssoc[$menuId])) {
                    $this->activeMenuItem = $menuArrAssoc[$menuId];
                }
            }
        }
    }

    public function prepareMenuArrAssoc(): void
    {
        if ($this->activeMenuItem) {

            $menuArrAssoc = $this->getMenuArr();
            $currLng = $this->request->getLocale();
            $menuSingleArrAssoc = $this->getMenuSingleArrAssoc();
            //
            //            if ($this->activeMenuItem['discr'] == 'redirect_page') {
            ////                dd($this->activeMenuItem);
            //                //TODO da se redirectne!!
            //            }

            $this->menuItemsPath = [];
            $menuItem = $this->activeMenuItem;

            $bubbleFields = [
                'skin' => null,
                'headHtml' => null,
                'preBodyHtml' => null,
                'posBodyHtml' => null,
            ];

            $bubbleLngFields = [
                'headHtml' => null,
                'preBodyHtml' => null,
                'posBodyHtml' => null,
            ];

            while ($menuItem) {
                $menuItem['selected'] = true;
                if (isset($this->menuArrAssoc[$menuItem['id']])) {
                    $this->menuArrAssoc[$menuItem['id']]['selected'] = true;
                }
                $this->menuItemsPath[] = $menuItem;

                foreach ($bubbleFields as $field => $val) {
                    if (empty($val)) $bubbleFields[$field] = $menuItem[$field];
                }

                foreach ($bubbleLngFields as $field => $val) {
                    if (empty($val) && !empty($menuItem['lngs'][$currLng][$field])) $bubbleLngFields[$field] = $menuItem['lngs'][$currLng][$field];
                }


                if (isset($menuSingleArrAssoc[$menuItem['id']]['parentId']) && isset($menuSingleArrAssoc[$menuSingleArrAssoc[$menuItem['id']]['parentId']])) {
                    $menuItem = $menuArrAssoc[$menuSingleArrAssoc[$menuItem['id']]['parentId']];
                } else {
                    $menuItem = null;
                }
            }

            foreach ($bubbleFields as $field => $val) {
                $this->activeMenuItem[$field] = $val;
            }
            foreach ($bubbleLngFields as $field => $val) {
                $this->activeMenuItem['lngs'][$currLng][$field] = $val;
            }
        }
    }



    //    public function getAsSelectOptions()
    //    {
    //        $options = [];
    //        $menus = $this->getMenuSingleArr();
    //        foreach ($menus as $menu) {
    //            $options[$menu['id']] = str_repeat('  ', $menu['lvl'])."{$menu['title']} ({$menu['id']})";
    //        }
    //
    //        return $options;
    //    }


    function getUrlForPageId($pageId, ?string $currentLng = null): ?string
    {
        if ($currentLng === null && $this->request) {
            $currentLng = $this->request->getLocale();
        }

        $menuArr = $this->getMenuArr();
        if (!isset($menuArr[$pageId])) {
            return null;
        }

        if ($currentLng && isset($menuArr[$pageId]['lngs'][$currentLng]['url']) && $menuArr[$pageId]['lngs'][$currentLng]['url']) {
            return $menuArr[$pageId]['lngs'][$currentLng]['url'];
        }

        return $menuArr[$pageId]['url'] ?? "/pageId-{$pageId}";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMenusByTag(string $tag): array
    {
        $this->load();
        $menus = $this->getMenuArr();
        $locale = $this->request?->getLocale() ?? 'bg';
        $result = [];

        foreach ($menus as $id => $menu) {
            $tags = array_filter(array_map('trim', explode(',', (string) ($menu['tags'] ?? ''))));
            if (!in_array($tag, $tags, true)) {
                continue;
            }
            if (!isset($menu['lngs'][$locale]['isActive']) || empty($menu['lngs'][$locale]['isActive'])) {
                continue;
            }
            $result[$id] = $menu;
        }

        uasort($result, static fn (array $a, array $b): int => ($a['lft'] ?? 0) <=> ($b['lft'] ?? 0));

        return $result;
    }

    public function getActiveMenuItem()
    {
        return $this->activeMenuItem;
    }

    public function getMenuItemPath()
    {
        return $this->menuItemsPath;
    }
}
