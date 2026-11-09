<?php

namespace App\Controller\Website\Menu;

use App\Entity\Website\Menu\Menu;
use App\Entity\Website\Menu\MenuLng;
use App\Repository\Website\MarkItemSeriesPageRepository;
use App\Service\Website\FormLanguageService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[Route(path: '/website/menus', name: 'website_menu_')]
class MenuController extends AbstractController
{


    public function __construct(private TranslatorInterface $translator) {}

    #[Route(path: '/{page<\d+>?1}', name: 'list')]
    public function index(EntityManagerInterface $em)
    {
        return $this->render('website/menu/index.html.twig', []);
    }

    public function addUrlInElements(&$tree)
    {
        if (is_array($tree)) {
            if (!empty($tree['children'])) {
                foreach ($tree['children'] as &$v) {
                    if (!empty($v['children'])) {
                        $this->addUrlInElements($v);
                    }
                    $v['goToModule'] = '';
                    //                    if ($v['pageModule'] == 'news') {
                    //                        $v['goToModule'] = $this->generateUrl('admin_news_page_list', ['menuId' => $v['id']]);
                    //                    }
                }
            }
        }
    }

    #[Route(path: '/get-all-nodes', name: 'get_tree')]
    public function getAllNodes(EntityManagerInterface $em)
    {
        $repo = $em->getRepository(Menu::class);
        $repo->setChildrenIndex('children');
        $tree = $repo->childrenHierarchy();
        //        $this->addUrlInElements($tree[0]);
        return new JsonResponse($tree);
    }

    #[Route(path: '/tree', name: 'tree')]
    public function tree(EntityManagerInterface $em, Request $request)
    {

        if ($request->isMethod('POST')) {
            $post = $request->request->all();
            if (!empty($post['menu'])) {
                if (isset($post['addAfter'])) {
                    return $this->redirectToRoute('website_menu_tree_add_after', [
                        'id' => $post['menu'],
                        'pageModule' => $post['addAfter']
                    ]);
                }
                if (isset($post['addChild'])) {
                    return $this->redirectToRoute('website_menu_tree_add_as_child', [
                        'id' => $post['menu'],
                        'pageModule' => $post['addChild']
                    ]);
                }
            }
        }
        $menus = $em->getRepository(Menu::class)->getMenuAll()->getResult();
        $pageModules = $this->getParameter('page_modules');
        $preparePageModules = [];
        if (!empty($pageModules)) {
            $discriminators = $em->getClassMetadata(Menu::class)->discriminatorMap;
            $discriminatorsFlip = array_flip($discriminators);

            foreach ($pageModules as $pageModuleKey => $pageModule) {
                $preparePageModules[$pageModuleKey] = $pageModule;
                if (!isset($pageModule['module_multiple'])) {
                    foreach ($menus as $menu) {
                        if (isset($discriminatorsFlip[get_class($menu)]) && $discriminatorsFlip[get_class($menu)] == $pageModuleKey) {
                            unset($preparePageModules[$pageModuleKey]);
                            break;
                        }
                    }
                }
            }
        }
        return $this->render('website/menu/tree.html.twig', [
            'menus' => $menus,
            'pageModules' => $preparePageModules,
        ]);
    }

    public function flatten($arr, $parentId = null)
    {
        $result = [];
        foreach ($arr as $item) {
            if (isset($item['children']) && !empty($item['children'])) {
                $result = array_merge($result, $this->flatten($item['children'], $item['id']));
            }
            unset($item['children']);
            $item['parentId'] = $parentId;
            $result[] = $item;
        }
        return $result;
    }

    #[Route(path: '/tree/reorder', name: 'reorder')]
    public function treeReorder(Request $request, EntityManagerInterface $em)
    {
        $json = $request->getContent();
        $menu = json_decode($json, true);
        $repo = $em->getRepository(Menu::class);
        $repo->reorderAll();
        $menu = $this->flatten($menu);
        if (!empty($menu)) {
            foreach ($menu as $menuItem) {
                if ($menuItem['parentId'] > 0) {
                    $tmpParentNode = $repo->find($menuItem['parentId']);
                    if ($repo->getChildren()) {
                        $repo->persistAsLastChildOf($repo->find($menuItem['id']), $tmpParentNode);
                    } else {
                        $repo->persistAsFirstChildOf($repo->find($menuItem['id']), $tmpParentNode);
                    }
                    $em->flush();
                }
            }
        }
        $repo->setChildrenIndex('children');
        $tree = $repo->childrenHierarchy();
        return new JsonResponse($tree);
    }

    #[Route(path: '/tree/{id<\d+>?-1}/add-after/{pageModule}', name: 'tree_add_after')]
    public function addAfter(Request $request, $id, EntityManagerInterface $em, $pageModule)
    {
        $pageModules = $this->getParameter('page_modules');
        if (!isset($pageModules[$pageModule])) {
            throw $this->createNotFoundException('Modula ne e dobaven v service yaml faila!');
        }
        $discriminators = $em->getClassMetadata(Menu::class)->discriminatorMap;
        if (!isset($discriminators[$pageModule])) {
            throw $this->createNotFoundException('Modula ne e dobaven v entity discriminator!');
        }
        $baseClassName = (new \ReflectionClass($discriminators[$pageModule]))->getShortName();

        $menuEntityClassName = 'App\\Entity\\Website\\Menu\\' . $baseClassName;
        /**
         * @var Menu $mainObj
         */
        $mainObj = new $menuEntityClassName;
        foreach ($this->getParameter('languages') as $lng) {
            /**
             * @var MenuLng $lngObj
             */
            $lngClass = $menuEntityClassName . 'Lng';
            $lngObj = new $lngClass();
            $lngObj->setLng($lng);
            $mainObj->addLng($lngObj);
        }
        $form = $this->createForm('App\\Form\\Website\\Menu\\' . $baseClassName . 'Form', $mainObj, [
            'pageModuleOptions' => $this->getParameter('page_modules'),
            'menuTagOptions' => $this->getParameter('menu_tags'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $menuRepo = $em->getRepository(Menu::class);
                $menuItem = $menuRepo->find($id);
                $em->persist($mainObj);
                $menuRepo->persistAsNextSiblingOf($mainObj, $menuItem);
                $em->flush();

                $this->addFlash('success', $this->translator->trans('layout.successCreateFlashMsg'));
                return $this->redirectToRoute('website_menu_tree');
            }
        }

        return $this->render('website/menu/create.html.twig', [
            'form' => $form->createView(),
            'pageModule' => $pageModule,
        ]);
    }

    #[Route(path: '/tree/{id<\d+>?-1}/add-as-child/{pageModule}', name: 'tree_add_as_child')]
    public function addAsChild(Request $request, $id, EntityManagerInterface $em, $pageModule)
    {
        $pageModules = $this->getParameter('page_modules');
        if (!isset($pageModules[$pageModule])) {
            throw $this->createNotFoundException('Modula ne e dobaven v service yaml faila!');
        }
        $discriminators = $em->getClassMetadata(Menu::class)->discriminatorMap;
        if (!isset($discriminators[$pageModule])) {
            throw $this->createNotFoundException('Modula ne e dobaven v entity discriminator!');
        }
        $baseClassName = (new \ReflectionClass($discriminators[$pageModule]))->getShortName();
        $menuEntityClassName = 'App\\Entity\\Website\\Menu\\' . $baseClassName;
        /**
         * @var Menu $mainObj
         */
        $mainObj = new $menuEntityClassName;
        foreach ($this->getParameter('languages') as $lng) {
            /**
             * @var MenuLng $lngObj
             */
            $lngClass = $menuEntityClassName . 'Lng';
            $lngObj = new $lngClass();
            $lngObj->setLng($lng);
            $mainObj->addLng($lngObj);
        }
        $form = $this->createForm('App\\Form\\Website\\Menu\\' . $baseClassName . 'Form', $mainObj, [
            'pageModuleOptions' => $this->getParameter('page_modules'),
            'menuTagOptions' => $this->getParameter('menu_tags'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $menuRepo = $em->getRepository(Menu::class);
                $menuItem = $menuRepo->find($id);
                $em->persist($mainObj);
                $menuRepo->persistAsFirstChildOf($mainObj, $menuItem);
                $em->flush();

                $this->addFlash('success', $this->translator->trans('layout.successCreateFlashMsg'));
                return $this->redirectToRoute('website_menu_tree');
            }
        }
        return $this->render('website/menu/create.html.twig', [
            'form' => $form->createView(),
            'pageModule' => $pageModule,
        ]);
    }

    #[Route(path: '/tree/{id<\d+>?-1}/delete', name: 'tree_delete')]
    public function delete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $menu = $em->getRepository(Menu::class)->find($id);
        if (!$menu) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            $em->getConnection()->beginTransaction();
            try {
                $em->remove($menu);
                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
            } catch (ForeignKeyConstraintViolationException) {
                $em->getConnection()->rollBack();
                $this->addFlash('error', $this->translator->trans('layout.errorDeleteFlashMsg'));
            }

            return $this->redirectToRoute('website_menu_tree');
        }

        return $this->render('website/menu/delete_confirm.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route(path: '/tree/{id<\d+>?-1}/edit', name: 'tree_edit')]
    public function edit(Request $request, $id, EntityManagerInterface $em, FormLanguageService $fls)
    {

        /**
         * @var Menu $mainObj
         */
        $mainObj = $em->getRepository(Menu::class)->getMenuWithLng($id, null, $this->getParameter('languages'));
        //        dd($em->getClassMetadata(Menu::class), $em->getClassMetadata(MenuLng::class));
        $mainObj = $fls->addMissingLngToForm($mainObj);
        $baseClassName = (new \ReflectionClass($mainObj))->getShortName();
        $discriminators = $em->getClassMetadata(Menu::class)->discriminatorMap;
        $pageModule = array_search(get_class($mainObj), $discriminators);
        $form = $this->createForm('App\\Form\\Website\\Menu\\' . $baseClassName . 'Form', $mainObj, [
            'pageModuleOptions' => $this->getParameter('page_modules'),
            'menuTagOptions' => $this->getParameter('menu_tags'),
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            $formLng = $form->get('lngs');

//            foreach ($form->get('lngs') as $lngData) {
//                if ($lngData->get('isActive')->getData()) {
//                    if (empty($lngData->get('contentTitle')->getData())) {
//                        $lngData->get('contentTitle')->addError(new FormError($this->translator->trans('admin.module.contentTitle.required')));
//                    }
//                    if (empty($lngData->get('menuTitle')->getData())) {
//                        $lngData->get('menuTitle')->addError(new FormError($this->translator->trans('admin.module.menuTitle.required')));
//                    }
//                }
//            }

            /**
             * @var SubmitButton $deleteBtn
             */
            $deleteBtn = $form->get('delete');
            if ($deleteBtn->isClicked()) {
                $em->getConnection()->beginTransaction();
                try {
                    $em->remove($mainObj);
                    $em->flush();
                    $em->getConnection()->commit();
                    $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
                } catch (ForeignKeyConstraintViolationException $e) {
                    $em->getConnection()->rollBack();
                    $this->addFlash('error', $this->translator->trans('layout.errorDeleteFlashMsg'));
                }
                return $this->redirectToRoute('website_menu_tree');
            }

            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked() && $form->isValid()) {
                $em->flush();
                $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                return $this->redirectToRoute('website_menu_tree');
            }
        }

        return $this->render('website/menu/edit.html.twig', [
            'form' => $form->createView(),
            'pageModule' => $pageModule,
        ]);
    }

    #[Route(path: '/clear-cache', name: 'clear_cache')]
    public function clearCache()
    {
        // Стартиране на командата cache:clear чрез Process
        $process = new Process([$this->getParameter('kernel.environment') === 'prod' ? '/opt/cpanel/ea-php82/root/usr/bin/php' : 'php', 'bin/console', 'cache:clear']);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir')); // Работете в коректната директория на проекта

        try {
            $process->mustRun();
            return new Response('Cache was successfully cleared!');
        } catch (ProcessFailedException $exception) {
            return new Response('Error while clearing cache: ' . $exception->getMessage());
        }
    }
}
