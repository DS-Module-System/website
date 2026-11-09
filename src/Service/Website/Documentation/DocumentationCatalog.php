<?php

namespace App\Service\Website\Documentation;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationCatalog
{
    /**
     * @param array<string, array{
     *     title: array{bg: string, en: string}|string,
     *     description?: array{bg: string, en: string}|string,
     *     modules: array<string, mixed>
     * }> $sections
     */
    public function __construct(
        #[Autowire('%documentation.root%')]
        private readonly string $root,
        #[Autowire('%documentation.sections%')]
        private readonly array $sections,
    ) {
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @return list<array{key: string, title: string, description: string, modules: list<array<string, mixed>>}>
     */
    public function getSections(string $locale = 'bg'): array
    {
        $locale = $this->normalizeLocale($locale);
        $result = [];
        foreach ($this->sections as $key => $section) {
            $result[] = [
                'key' => $key,
                'title' => $this->localized($section['title'] ?? $key, $locale),
                'description' => $this->localized($section['description'] ?? '', $locale),
                'modules' => $this->getModulesForSection($key, $locale),
            ];
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getModulesForSection(string $sectionKey, string $locale = 'bg'): array
    {
        $locale = $this->normalizeLocale($locale);
        if (!isset($this->sections[$sectionKey])) {
            return [];
        }

        $modules = [];
        foreach ($this->sections[$sectionKey]['modules'] as $key => $definition) {
            if ($this->isGroupDefinition($definition)) {
                $pages = [];
                $descriptionSummary = '';
                foreach ($definition['pages'] as $slug => $pageTitle) {
                    $path = $this->modulePath($sectionKey, $slug);
                    if (!is_dir($path)) {
                        continue;
                    }

                    $page = $this->buildModuleMeta(
                        $sectionKey,
                        $slug,
                        $this->localized($pageTitle, $locale),
                        $path,
                        $locale,
                        $key,
                        $this->localized($definition['title'] ?? $key, $locale),
                    );
                    $page['type'] = 'page';
                    $pages[] = $page;

                    if ($descriptionSummary === '' && str_ends_with($slug, '-module')) {
                        $descriptionSummary = $page['summary'];
                    }
                }

                if ($pages === []) {
                    continue;
                }

                if ($descriptionSummary === '') {
                    $descriptionSummary = $pages[array_key_last($pages)]['summary'] ?? '';
                }

                $modules[] = [
                    'type' => 'group',
                    'section' => $sectionKey,
                    'key' => $key,
                    'title' => $this->localized($definition['title'] ?? $key, $locale),
                    'summary' => $descriptionSummary,
                    'pages' => $pages,
                ];

                continue;
            }

            $path = $this->modulePath($sectionKey, $key);
            if (!is_dir($path)) {
                continue;
            }

            $page = $this->buildModuleMeta(
                $sectionKey,
                $key,
                $this->localized($definition, $locale),
                $path,
                $locale
            );
            $page['type'] = 'page';
            $modules[] = $page;
        }

        return $modules;
    }

    /**
     * @return array{
     *     section: string,
     *     sectionTitle: string,
     *     slug: string,
     *     title: string,
     *     summary: string,
     *     markdownPath: string,
     *     content: string,
     *     diagrams: list<array{file: string, label: string}>,
     *     groupKey: string|null,
     *     groupTitle: string|null
     * }
     */
    public function getModule(string $sectionKey, string $slug, string $locale = 'bg'): array
    {
        $locale = $this->normalizeLocale($locale);
        $resolved = $this->resolvePageDefinition($sectionKey, $slug, $locale);
        if ($resolved === null) {
            throw new NotFoundHttpException('Documentation module not found.');
        }

        $path = $this->modulePath($sectionKey, $slug);
        if (!is_dir($path)) {
            throw new NotFoundHttpException('Documentation module not found.');
        }

        $markdownPath = $this->findMarkdownFile($path, $locale);
        if ($markdownPath === null) {
            throw new NotFoundHttpException('Documentation markdown not found.');
        }

        $diagrams = [];
        foreach ($this->findDiagramFiles($path) as $file) {
            $diagrams[] = [
                'file' => $file,
                'label' => $this->diagramLabel($file, $locale),
            ];
        }

        $pageTitle = $this->localized($resolved['title'], $locale);
        $displayTitle = $resolved['groupTitle'] !== null
            ? $resolved['groupTitle'] . ' — ' . $pageTitle
            : $pageTitle;

        return [
            'section' => $sectionKey,
            'sectionTitle' => $this->localized($this->sections[$sectionKey]['title'] ?? $sectionKey, $locale),
            'slug' => $slug,
            'title' => $displayTitle,
            'pageTitle' => $pageTitle,
            'summary' => $this->readSummary($path, $locale),
            'markdownPath' => $markdownPath,
            'content' => file_get_contents($markdownPath) ?: '',
            'diagrams' => $diagrams,
            'groupKey' => $resolved['groupKey'],
            'groupTitle' => $resolved['groupTitle'],
        ];
    }

    public function resolveAssetPath(string $sectionKey, string $slug, string $file): string
    {
        if ($this->resolvePageDefinition($sectionKey, $slug, 'bg') === null) {
            throw new NotFoundHttpException('Documentation asset not found.');
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+\.png$/i', $file)) {
            throw new NotFoundHttpException('Documentation asset not found.');
        }

        $path = realpath($this->modulePath($sectionKey, $slug) . DIRECTORY_SEPARATOR . $file);
        $moduleReal = realpath($this->modulePath($sectionKey, $slug));
        $rootReal = realpath($this->root);

        if ($path === false || $moduleReal === false || $rootReal === false) {
            throw new NotFoundHttpException('Documentation asset not found.');
        }

        if (!str_starts_with($path, $moduleReal) || !str_starts_with($moduleReal, $rootReal)) {
            throw new NotFoundHttpException('Documentation asset not found.');
        }

        if (!is_file($path)) {
            throw new NotFoundHttpException('Documentation asset not found.');
        }

        return $path;
    }

    private function modulePath(string $sectionKey, string $slug): string
    {
        return $this->root . DIRECTORY_SEPARATOR . $sectionKey . DIRECTORY_SEPARATOR . $slug;
    }

    /**
     * @return array{
     *     title: array{bg: string, en: string}|string,
     *     groupKey: string|null,
     *     groupTitle: string|null
     * }|null
     */
    private function resolvePageDefinition(string $sectionKey, string $slug, string $locale): ?array
    {
        if (!isset($this->sections[$sectionKey]['modules'])) {
            return null;
        }

        $modules = $this->sections[$sectionKey]['modules'];

        if (isset($modules[$slug]) && !$this->isGroupDefinition($modules[$slug])) {
            return [
                'title' => $modules[$slug],
                'groupKey' => null,
                'groupTitle' => null,
            ];
        }

        foreach ($modules as $groupKey => $definition) {
            if (!$this->isGroupDefinition($definition)) {
                continue;
            }

            if (!isset($definition['pages'][$slug])) {
                continue;
            }

            return [
                'title' => $definition['pages'][$slug],
                'groupKey' => (string) $groupKey,
                'groupTitle' => $this->localized($definition['title'] ?? $groupKey, $locale),
            ];
        }

        return null;
    }

    private function isGroupDefinition(mixed $definition): bool
    {
        return is_array($definition) && isset($definition['pages']) && is_array($definition['pages']);
    }

    /**
     * @return array{
     *     section: string,
     *     slug: string,
     *     title: string,
     *     summary: string,
     *     hasMarkdown: bool,
     *     diagrams: list<string>,
     *     groupKey: string|null,
     *     groupTitle: string|null
     * }
     */
    private function buildModuleMeta(
        string $sectionKey,
        string $slug,
        string $title,
        string $path,
        string $locale,
        ?string $groupKey = null,
        ?string $groupTitle = null,
    ): array {
        return [
            'section' => $sectionKey,
            'slug' => $slug,
            'title' => $title,
            'summary' => $this->readSummary($path, $locale),
            'hasMarkdown' => $this->findMarkdownFile($path, $locale) !== null,
            'diagrams' => $this->findDiagramFiles($path),
            'groupKey' => $groupKey,
            'groupTitle' => $groupTitle,
        ];
    }

    private function findMarkdownFile(string $path, string $locale): ?string
    {
        if ($locale === 'en') {
            $finder = new Finder();
            $finder->files()->in($path)->name('*.en.md')->depth(0)->sortByName();
            foreach ($finder as $file) {
                return $file->getRealPath() ?: null;
            }
        }

        $finder = new Finder();
        $finder->files()->in($path)->name('*.md')->notName('*.en.md')->depth(0)->sortByName();
        foreach ($finder as $file) {
            return $file->getRealPath() ?: null;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function findDiagramFiles(string $path): array
    {
        $preferred = ['class.png', 'sequence.png', 'simple.png'];
        $found = [];
        foreach ($preferred as $name) {
            if (is_file($path . DIRECTORY_SEPARATOR . $name)) {
                $found[] = $name;
            }
        }

        if ($found !== []) {
            return $found;
        }

        $finder = new Finder();
        $finder->files()->in($path)->name('*.png')->depth(0)->sortByName();
        foreach ($finder as $file) {
            $found[] = $file->getFilename();
        }

        return $found;
    }

    private function readSummary(string $path, string $locale): string
    {
        $candidates = $locale === 'en'
            ? ['summary.en.txt', 'summary.txt']
            : ['summary.txt'];

        foreach ($candidates as $name) {
            $summaryFile = $path . DIRECTORY_SEPARATOR . $name;
            if (is_file($summaryFile)) {
                return trim((string) file_get_contents($summaryFile));
            }
        }

        return '';
    }

    private function diagramLabel(string $file, string $locale): string
    {
        $labels = [
            'class.png' => ['bg' => 'Компонентна архитектура', 'en' => 'Component architecture'],
            'sequence.png' => ['bg' => 'Sequence диаграма', 'en' => 'Sequence diagram'],
            'simple.png' => ['bg' => 'Опростен поток', 'en' => 'Simplified flow'],
        ];

        $key = strtolower($file);
        if (isset($labels[$key])) {
            return $labels[$key][$locale] ?? $labels[$key]['en'];
        }

        return pathinfo($file, PATHINFO_FILENAME);
    }

    private function normalizeLocale(string $locale): string
    {
        return str_starts_with(strtolower($locale), 'en') ? 'en' : 'bg';
    }

    private function localized(array|string $value, string $locale): string
    {
        if (is_string($value)) {
            return $value;
        }

        return (string) ($value[$locale] ?? $value['bg'] ?? $value['en'] ?? '');
    }
}
