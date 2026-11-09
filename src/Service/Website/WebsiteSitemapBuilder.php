<?php

namespace App\Service\Website;

use App\Entity\Website\Blog\Blog;
use App\Repository\Website\Blog\BlogRepository;
use App\Service\Website\Documentation\DocumentationCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebsiteSitemapBuilder
{
    /**
     * @param list<string> $languages
     * @param array<string, array<string, mixed>> $pageModules
     */
    public function __construct(
        private readonly MenuService $menuService,
        private readonly BlogRepository $blogRepository,
        private readonly DocumentationCatalog $catalog,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%languages%')]
        private readonly array $languages,
        #[Autowire('%default_language%')]
        private readonly string $defaultLanguage,
        #[Autowire('%page_modules%')]
        private readonly array $pageModules,
    ) {
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    public function build(): array
    {
        $entries = [];
        $seen = [];

        foreach ($this->buildCmsEntries() as $entry) {
            $this->pushEntry($entries, $seen, $entry);
        }

        foreach ($this->buildDocumentationEntries() as $entry) {
            $this->pushEntry($entries, $seen, $entry);
        }

        return $entries;
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function buildCmsEntries(): array
    {
        $menus = $this->menuService->getMenuArr() ?? [];
        $entries = [];
        $activePosts = $this->blogRepository->findActive();

        foreach ($menus as $menu) {
            $discr = $menu['discr'] ?? 'menu';
            if ($discr === 'menu' || $discr === 'redirect_page') {
                continue;
            }

            if (!isset($this->pageModules[$discr])) {
                continue;
            }

            $alternates = [];
            foreach ($this->languages as $locale) {
                if (empty($menu['lngs'][$locale])) {
                    continue;
                }

                $lngRow = $menu['lngs'][$locale];
                if (array_key_exists('isActive', $lngRow) && !$lngRow['isActive']) {
                    continue;
                }

                $params = [];
                if (!empty($this->pageModules[$discr]['urls'])) {
                    $params['token'] = '';
                }

                try {
                    $alternates[$locale] = $this->urlGenerator->generate(
                        sprintf('cms_menu_%s.%s', $menu['id'], $locale),
                        $params,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                } catch (\Throwable) {
                    continue;
                }
            }

            if ($alternates === []) {
                continue;
            }

            $entries[] = $this->entryFromAlternates($alternates);

            if ($discr === 'blog_page') {
                foreach ($activePosts as $post) {
                    $postAlternates = [];
                    foreach (array_keys($alternates) as $locale) {
                        try {
                            $postAlternates[$locale] = $this->urlGenerator->generate(
                                sprintf('cms_menu_%s.%s', $menu['id'], $locale),
                                ['token' => $post->getSlug()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            );
                        } catch (\Throwable) {
                            continue;
                        }
                    }

                    if ($postAlternates === []) {
                        continue;
                    }

                    $entries[] = $this->entryFromAlternates($postAlternates, $this->blogLastmod($post));
                }
            }
        }

        return $entries;
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function buildDocumentationEntries(): array
    {
        $entries = [];

        $indexAlternates = [];
        foreach ($this->languages as $locale) {
            $indexAlternates[$locale] = $this->urlGenerator->generate(
                'website_documentation_index',
                ['_locale' => $locale],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        $entries[] = $this->entryFromAlternates($indexAlternates);

        $sections = $this->catalog->getSections($this->defaultLanguage);
        foreach ($sections as $section) {
            foreach ($section['modules'] as $module) {
                $pages = ($module['type'] ?? null) === 'group'
                    ? ($module['pages'] ?? [])
                    : [$module];

                foreach ($pages as $page) {
                    if (empty($page['hasMarkdown'])) {
                        continue;
                    }

                    $alternates = [];
                    foreach ($this->languages as $locale) {
                        $alternates[$locale] = $this->urlGenerator->generate(
                            'website_documentation_show',
                            [
                                'section' => $page['section'],
                                'slug' => $page['slug'],
                                '_locale' => $locale,
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }

                    $entries[] = $this->entryFromAlternates($alternates);
                }
            }
        }

        return $entries;
    }

    /**
     * @param array<string, string> $alternates
     *
     * @return array{loc: string, lastmod: ?string, alternates: array<string, string>}
     */
    private function entryFromAlternates(array $alternates, ?string $lastmod = null): array
    {
        $loc = $alternates[$this->defaultLanguage] ?? reset($alternates);

        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'alternates' => $alternates,
        ];
    }

    private function blogLastmod(Blog $post): ?string
    {
        $date = $post->getUpdatedAt() ?? $post->getCreatedAt();

        return $date?->format('Y-m-d');
    }

    /**
     * @param list<array{loc: string, lastmod: ?string, alternates: array<string, string>}> $entries
     * @param array<string, true> $seen
     * @param array{loc: string, lastmod: ?string, alternates: array<string, string>} $entry
     */
    private function pushEntry(array &$entries, array &$seen, array $entry): void
    {
        if (isset($seen[$entry['loc']])) {
            return;
        }

        $seen[$entry['loc']] = true;
        $entries[] = $entry;
    }
}
