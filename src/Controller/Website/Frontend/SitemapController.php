<?php

namespace App\Controller\Website\Frontend;

use App\Service\Website\WebsiteSitemapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'website_sitemap', methods: ['GET'], priority: 10)]
    public function index(WebsiteSitemapBuilder $sitemapBuilder): Response
    {
        $response = $this->render('website/frontend/sitemap.xml.twig', [
            'urls' => $sitemapBuilder->build(),
        ]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }
}
