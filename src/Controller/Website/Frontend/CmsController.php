<?php

namespace App\Controller\Website\Frontend;

use App\Service\Website\CMSUtils;
use App\Service\Website\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CmsController extends AbstractController
{
    public function __construct(
        public MenuService $menuService,
        public EntityManagerInterface $em,
        public CMSUtils $CMSUtils,
        public PaginatorInterface $paginator
    ) {
        $this->menuService->load();
        //        dd($this->menuService->getMenuArr());
    }

    #[Route('/pageId-{id}/{token}', name: 'cms_page_id_to_redirect', priority: -1, requirements: ['token' => '.+'])]
    public function cmsp(Request $request, CMSUtils $CMSUtils, $id, $token = ''): Response
    {
        $menusArr = $this->menuService->getMenuArr();
        if (isset($menusArr[$id])) {

            //            $params = [
            //                'menuId'=>$id
            //            ];
            //
            //            if($token) {
            //                $params['token'] = $token;
            //            }

            $getParams = $request->query->all();

            $url = $this->generateUrl($CMSUtils->generateCmsRouteNameById($id));
            if (!empty($token)) {
                $url .= "/{$token}";
            }
            if (!empty($getParams)) {
                $url .= '?' . http_build_query($getParams, null, '&');
            }

            return $this->redirect($url);
        }

        throw $this->createNotFoundException();
    }
}
