<?php

namespace App\Controller\Website\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RobotsController extends AbstractController
{
    public function __construct(
        #[Autowire('%erp_prefix%')]
        private readonly string $erpPrefix,
    ) {
    }

    #[Route('/robots.txt', name: 'website_robots', methods: ['GET'], priority: 10)]
    public function index(Request $request): Response
    {
        $adminPath = '/' . trim($this->erpPrefix !== '' ? $this->erpPrefix : '/admin', '/');
        $sitemapUrl = $request->getSchemeAndHttpHost() . '/sitemap.xml';

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: ' . $adminPath . '/',
            'Disallow: /en' . $adminPath . '/',
            '',
            'Sitemap: ' . $sitemapUrl,
            '',
        ]);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
