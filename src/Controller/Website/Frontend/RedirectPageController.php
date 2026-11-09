<?php

namespace App\Controller\Website\Frontend;

use App\Entity\Website\Menu\Menu;
use App\Entity\Website\Menu\RedirectPage;
use App\Entity\Website\Menu\RedirectPageLng;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectPageController extends CmsController implements CmsControllerImpl
{
    public function index(Request $request, string $menuId, ?string $token = null): Response
    {
        $menu = $this->em->getRepository(Menu::class)->find($menuId);
        if (!$menu instanceof RedirectPage) {
            throw $this->createNotFoundException();
        }

        $locale = $request->getLocale();

        foreach ($menu->getLngs() as $lng) {
            if ($lng->getLng() !== $locale || !$lng instanceof RedirectPageLng) {
                continue;
            }
            if ($lng->getRedirectToUrl()) {
                return $this->redirect($lng->getRedirectToUrl());
            }
        }

        $redirectToMenu = $menu->getRedirectToMenu();
        if ($redirectToMenu) {
            $url = $this->CMSUtils->cmsUrlGenerateById((string) $redirectToMenu->getId());
            return $this->redirect($url);
        }

        throw $this->createNotFoundException('No redirect target configured for this page.');
    }
}
