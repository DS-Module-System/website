<?php

namespace App\Controller\Website\Frontend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomePageController extends CmsController implements CmsControllerImpl
{
    public function index(Request $request, string $menuId, ?string $token = null): Response
    {
        return $this->render('website/frontend/home_page/index.html.twig', [
            'menuId' => $menuId,
            'activeMenuItem' => $this->menuService->getActiveMenuItem(),
        ]);
    }
}
