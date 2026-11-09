<?php


namespace App\Routing\Website;

use App\Entity\Website\Menu\Menu;
use App\Service\Website\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CmsLoader extends Loader
{
    private bool $isLoaded = false;

    private ?RouteCollection $routeCollection = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private array $pageModules,
        private readonly array $languages,
        private readonly string $defaultLanguage,
        private MenuService $menuService
    ) {
        parent::__construct();
    }

    public function load($resource, $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            return $this->routeCollection;
        }

        $menus = $this->menuService->getMenuArr();
        /**
         * @var Menu[] $menusEntity
         */
        $menusEntity = $this->em->getRepository(Menu::class)->getMenuAll()->getResult();
        $prepareEntity = [];
        if ($menusEntity) {
            foreach ($menusEntity as $item) {
                $prepareEntity[$item->getId()] = $item;
            }
        }

        //        echo '<pre>'.print_r($menus, true).'</pre>';die();
        //        dd($menus);
        //dd(2);
        $routes = new RouteCollection();

        foreach ($menus as $menu) {
            foreach ($this->languages as $lng) {
                $defaults = [
                    'menuId' => $menu['id'],
                    '_canonical_route' => "cms_menu_{$menu['id']}",
                    '_locale' => $lng,
                ];
                $requirements = [];
                $routePath = $menu['url'];

                if ($this->defaultLanguage != $lng) {
                    $routePath = "/{$lng}{$routePath}";
                }

                if (isset($menu['lngs'][$lng])) {
                    $menuLng = $menu['lngs'][$lng];

                    $routePath = $menuLng['url'];
                    if ($this->defaultLanguage != $lng) {
                        $routePath = "/{$lng}{$routePath}";
                    }

                    $discriminatorMap = $this->em->getClassMetadata(Menu::class)->discriminatorMap;
                    $pageModule = array_search($prepareEntity[$menu['id']]::class, $discriminatorMap);

                    if (!isset($this->pageModules[$pageModule])) {
                        throw new \Exception('Nqma popalneno pole module v menu: ' . $menu->getTitle());
                    }

                    $pageModuleConfig = $this->pageModules[$pageModule];

                    $defaults['_controller'] = $pageModuleConfig['fe_class'];

                    if (isset($pageModuleConfig['urls'])) {
                        $defaults['token'] = '';
                        $routePath .= "/{token}";
                        $requirements = [
                            'token' => '.+',
                        ];
                    }
                }

                $route = new Route($routePath, $defaults, $requirements);

                // add the new route to the route collection
                $routeName = "cms_menu_{$menu['id']}.{$lng}";
                $routes->add($routeName, $route);
            }
        }

        $this->isLoaded = true;
        $this->routeCollection = $routes;

        return $routes;
    }

    public function supports($resource, $type = null): bool
    {
        return 'cms' === $type;
    }
}
