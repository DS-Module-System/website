<?php

namespace App\Controller\Website\Blog;

use App\Entity\Website\Blog\Blog;
use App\Form\Website\Blog\BlogForm;
use App\Repository\Website\Blog\BlogRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/website/blog', name: 'website_blog_')]
class BlogController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly PaginatorInterface $paginator,
    ) {}

    #[Route(path: '/', name: 'list')]
    public function list(Request $request): Response
    {
        /** @var BlogRepository $repository */
        $repository = $this->em->getRepository(Blog::class);

        $qb = $repository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        $rows = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $request->query->getInt('ipp', 25)
        );

        return $this->render('website/blog/index.html.twig', [
            'rows' => $rows,
        ]);
    }

    #[Route(path: '/create', name: 'create')]
    public function create(Request $request): Response
    {
        $blog = new Blog();

        $form = $this->createForm(BlogForm::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $this->em->persist($blog);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('layout.successCreateFlashMsg'));
                return $this->redirectToRoute('website_blog_list');
            }
        }

        return $this->render('website/blog/form.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
            'isEdit' => false,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'edit')]
    public function edit(Request $request, string $id): Response
    {
        $blog = $this->em->getRepository(Blog::class)->find($id);
        if (!$blog) {
            throw $this->createNotFoundException('Blog post not found');
        }

        $form = $this->createForm(BlogForm::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $deleteBtn = $form->get('delete');
            if ($deleteBtn->isClicked()) {
                $this->em->getConnection()->beginTransaction();
                try {
                    $this->em->remove($blog);
                    $this->em->flush();
                    $this->em->getConnection()->commit();
                    $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->em->getConnection()->rollBack();
                    $this->addFlash('error', $this->translator->trans('layout.errorDeleteFlashMsg'));
                }
                return $this->redirectToRoute('website_blog_list');
            }

            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked() && $form->isValid()) {
                $this->em->flush();
                $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                return $this->redirectToRoute('website_blog_list');
            }
        }

        return $this->render('website/blog/form.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
            'isEdit' => true,
        ]);
    }
}
