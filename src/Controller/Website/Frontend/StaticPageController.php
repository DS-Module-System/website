<?php

namespace App\Controller\Website\Frontend;

use App\Controller\Website\Frontend\CmsController;
use App\Controller\Website\Frontend\CmsControllerImpl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticPageController extends CmsController implements CmsControllerImpl
{
    public function index(Request $request, string $menuId, ?string $token): Response
    {
        $activeMenuItem = $this->menuService->getActiveMenuItem();

        if ($menuId == 2) {
            return $this->render('website/frontend/static_page/modules.html.twig', [
                'menuId' => $menuId,
                'activeMenuItem' => $activeMenuItem,
            ]);
        }

        return $this->render('website/frontend/static_page/index.html.twig', [
            'menuId' => $menuId,
            'activeMenuItem' => $activeMenuItem,
        ]);
    }
}
