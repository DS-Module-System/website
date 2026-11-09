<?php

namespace App\Controller\Website\Frontend;

use App\Controller\Website\Frontend\CmsController;
use App\Controller\Website\Frontend\CmsControllerImpl;
use App\Entity\Website\Blog\Blog;
use App\Repository\Website\Blog\BlogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogPageController extends CmsController implements CmsControllerImpl
{
    public function index(Request $request, string $menuId, ?string $token): Response
    {
        $activeMenuItem = $this->menuService->getActiveMenuItem();

        /** @var BlogRepository $blogRepository */
        $blogRepository = $this->em->getRepository(Blog::class);

        if (!empty($token)) {
            $blog = $blogRepository->findOneBySlug($token);
            if (!$blog) {
                throw $this->createNotFoundException('Blog post not found');
            }

            return $this->render('website/frontend/blog_page/show.html.twig', [
                'menuId' => $menuId,
                'blog' => $blog,
                'activeMenuItem' => $activeMenuItem,
            ]);
        }

        $qb = $blogRepository->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.createdAt', 'DESC');

        $posts = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('website/frontend/blog_page/index.html.twig', [
            'menuId' => $menuId,
            'posts' => $posts,
            'activeMenuItem' => $activeMenuItem,
        ]);
    }
}
