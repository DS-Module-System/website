<?php

namespace App\Controller\Website\Frontend;

use App\Service\Website\Documentation\DocumentationCatalog;
use App\Service\Website\Documentation\DocumentationRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    public function __construct(
        private readonly DocumentationCatalog $catalog,
        private readonly DocumentationRenderer $renderer,
    ) {
    }

    #[Route('/documentation', name: 'website_documentation_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();

        return $this->render('website/frontend/documentation/index.html.twig', [
            'sections' => $this->catalog->getSections($locale),
            'docsLocale' => $locale,
        ]);
    }

    #[Route('/documentation/{section}/{slug}', name: 'website_documentation_show', methods: ['GET'], requirements: [
        'section' => '[a-z0-9\-]+',
        'slug' => '[a-z0-9\-]+',
    ])]
    public function show(Request $request, string $section, string $slug): Response
    {
        $locale = $request->getLocale();
        $module = $this->catalog->getModule($section, $slug, $locale);

        return $this->render('website/frontend/documentation/show.html.twig', [
            'sections' => $this->catalog->getSections($locale),
            'module' => $module,
            'html' => $this->renderer->toHtml($module['content']),
            'currentSection' => $section,
            'currentSlug' => $slug,
            'docsLocale' => $locale,
        ]);
    }

    #[Route('/documentation/asset/{section}/{slug}/{file}', name: 'website_documentation_asset', methods: ['GET'], requirements: [
        'section' => '[a-z0-9\-]+',
        'slug' => '[a-z0-9\-]+',
        'file' => '[a-zA-Z0-9._-]+\.png',
    ])]
    public function asset(string $section, string $slug, string $file): Response
    {
        $path = $this->catalog->resolveAssetPath($section, $slug, $file);
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'image/png');
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
