<?php

namespace App\Twig\Website;

use App\Entity\Website\Menu\Menu;
use App\Service\Website\CMSUtils;
use App\Service\Website\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CMSUtilsExtension extends AbstractExtension
{

    private $request;

    public function __construct(
        private EntityManagerInterface $em,
        private CMSUtils $CMSUtils,
        private MenuService $menuService,
        private RequestStack $requestStack
    )
    {
        $this->request = $requestStack->getCurrentRequest();
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('cms_path_by_id', [$this, 'cmsPathById']),
            new TwigFunction('get_active_menu_item', [$this, 'getActiveMenuItem']),
            new TwigFunction('get_menu_item_path', [$this, 'getMenuItemPath']),
            new TwigFunction('instanceOf', [$this, 'isInstanceOf']),
            new TwigFunction('is_edit_route', [$this, 'isEditRoute']),
            new TwigFunction('cms_get_menu_children', [$this, 'cmsGetMenuChildren']),
            new TwigFunction('cms_menu_tree', [$this, 'getMenuTree']),
            new TwigFunction('cms_menu_by_level', [$this, 'getMenuByLevel']),
            new TwigFunction('cms_menu_children_by_parent', [$this, 'getMenuChildrenByParent']),
            new TwigFunction('cms_menu_sibling_by_menu_id', [$this, 'getMenuSiblingByMenuId']),
            new TwigFunction('cms_menu_by_discr', [$this, 'getMenuByDiscr']),
            new TwigFunction('cms_path_by_discr', [$this, 'cmsPathByDiscr']),
            new TwigFunction('cms_menu_has_children', [$this, 'getMenuHasChildren']),
            new TwigFunction('cms_menus_by_tag', [$this, 'getMenusByTag']),
        ];
    }

    public function cmsPathById(string $pageId, array $parameters = [], bool $relative = false): string
    {

        $menus = $this->menuService->getMenuArr();
        if(isset($menus[$pageId]) && $menus[$pageId]['discr'] == 'redirect_page') {
            if($this->request instanceof Request && $this->request->getLocale() && isset($menus[$pageId]['lngs'][$this->request->getLocale()]['redirectToUrl'])) {
                return $menus[$pageId]['lngs'][$this->request->getLocale()]['redirectToUrl'];
            }
        }

        return $this->CMSUtils->cmsUrlGenerateById($pageId, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function getActiveMenuItem(): array
    {
        return $this->menuService->getActiveMenuItem() ?? [];
    }

    public function getMenuItemPath(): array
    {
        return $this->menuService->getMenuItemPath();
    }

    public function isInstanceOf(mixed $var, mixed $instance): bool
    {
        return $var instanceof $instance;
    }

    public function isEditRoute(string $route, string $editStr = ''): bool
    {
        if(empty($editStr)) {
            $editStr = '_edit';
        }

        return str_contains($route, $editStr);
    }

    public function cmsGetMenuChildren($menuId): array
    {
        $children = [];
        $menu = $this->em->getRepository(Menu::class)->find($menuId);
        if($menu) {
            $children = $this->em->getRepository(Menu::class)->children($menu);
        }

        return $children;
    }

    public function getMenuTree(): array
    {
        return $this->menuService->getMenuArr();

    }

    public function getMenuByLevel($level): array
    {
        $res = [];
        $menus = $this->menuService->getMenuArr();
        if($menus) {
            foreach ($menus as $k=>$menu) {
                if($menu['lvl'] == $level) {
                    $res[$k] = $menu;
                }
            }
        }
        return $res;
    }

    public function getMenuChildrenByParent($parentId): array
    {
        $res = [];
        $menus = $this->menuService->getMenuArr();
        if($menus) {
            foreach ($menus as $k=>$menu) {
                if($menu['parent_id'] == $parentId) {
                    $res[$k] = $menu;
                }
            }
        }
        return $res;
    }

    public function getMenuSiblingByMenuId($menuId): array
    {
        $res = [];
        $menus = $this->menuService->getMenuArr();
        if($menus) {
            if(isset($menus[$menuId])) {
                $parentId = $menus[$menuId]['parent_id'];
                foreach ($menus as $k=>$menu) {
                    if($menu['parent_id'] == $parentId) {
                        $res[$k] = $menu;
                    }
                }
            }
        }
        return $res;
    }

    public function getMenuByDiscr(?string $discr = null): array
    {
        $res = [];
        $menus = $this->menuService->getMenuArr();
        if ($menus) {
            foreach ($menus as $k => $menu) {
                if ($discr === null || ($menu['discr'] ?? '') === $discr) {
                    $res[$k] = $menu;
                }
            }
        }
        return $res;
    }

    public function cmsPathByDiscr(string $discr, array $parameters = [], bool $relative = false): ?string
    {
        $menus = $this->getMenuByDiscr($discr);
        $first = reset($menus);
        return $first ? $this->cmsPathById((string) $first['id'], $parameters, $relative) : null;
    }

    public function getMenuHasChildren($menuId): bool
    {
        $res = false;
        $menus = $this->menuService->getMenuArr();
        if($menus) {
            foreach ($menus as $k=>$menu) {
                if($menu['parent_id'] == $menuId) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMenusByTag(string $tag): array
    {
        return $this->menuService->getMenusByTag($tag);
    }
}
