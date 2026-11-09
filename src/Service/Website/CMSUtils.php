<?php

namespace App\Service\Website;

use App\Service\Website\MenuService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CMSUtils
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
        private MenuService $menuService
    ) {}

    static string $SORT_LIST_QUERY_NAME = 'sortQuery';

    public static function redirect(Request $request, RedirectResponse $redirectResponse, int $status = 302): RedirectResponse
    {
        $backUrlByClickBtn = $request->get('backUrlByClickBtn', null);
        if (!empty($backUrlByClickBtn)) {
            $backUrlByClickBtn = json_decode((string) $backUrlByClickBtn, true, 512, JSON_THROW_ON_ERROR);
            $foundMatchedRedirectResponse = null;
            foreach (array_reverse($backUrlByClickBtn) as $v) {
                $tmpParseUrl = parse_url((string) $v);
                preg_match('/^' . str_replace('/', '\/', $redirectResponse->getTargetUrl()) . '(\/[0-9]+)?$/', $tmpParseUrl['path'], $matches);
                if (isset($tmpParseUrl['path']) && isset($matches[0])) {
                    $foundMatchedRedirectResponse = $v;
                    break;
                }
            }

            if ($foundMatchedRedirectResponse !== null) {
                $backUrlByClickBtn = $foundMatchedRedirectResponse;

                $parseUrl = parse_url((string) $backUrlByClickBtn);
                if (isset($parseUrl['path']) && str_starts_with($parseUrl['path'], '/admin')) {
                    return new RedirectResponse($backUrlByClickBtn, $status);
                }
            }
        }

        return $redirectResponse;
    }

    /**
     * @param array<mixed> $orderBy
     */
    public static function setDefaultOrderInList(Request $request, array $orderBy, int $status = 302): ?RedirectResponse
    {
        $curUrl = $request->getUri();
        $parseUrl = parse_url($curUrl);

        if (!isset($parseUrl['query'])) {
            $parseUrl['query'] = '';
        }
        $newQuery = $parseUrl['query'];
        parse_str(urldecode($parseUrl['query']), $query);
        if (empty($query[self::$SORT_LIST_QUERY_NAME])) {
            if (!empty($orderBy)) {
                $query[self::$SORT_LIST_QUERY_NAME] = [];
                foreach ($orderBy as $col => $ord) {
                    $query[self::$SORT_LIST_QUERY_NAME][$col] = $ord;
                }
                $newQuery = http_build_query($query);
            }
        }
        $newUrl = "{$parseUrl['scheme']}://{$parseUrl['host']}{$parseUrl['path']}?{$newQuery}";
        if ($parseUrl['query'] != $newQuery) {
            return new RedirectResponse($newUrl, $status);
        }

        return null;
    }

    function fileUploadMaxSize()
    {
        $maxSize = -1;

        // Start with post_max_size.
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        if ($postMaxSize > 0) {
            $maxSize = $postMaxSize;
        }

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
        if ($uploadMax > 0 && $uploadMax < $maxSize) {
            $maxSize = $uploadMax;
        }
        return $maxSize;
    }

    function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = (float)preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }


    public function generateCmsRouteNameById($pageId): string
    {
        return "cms_menu_{$pageId}";
    }

    public function cmsUrlGenerateById(string $pageId, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $url = '/pageId-' . $pageId;

        $generateUrlStr = '';

        try {
            $generateUrlStr = $this->urlGenerator->generate($this->generateCmsRouteNameById($pageId), $parameters, $referenceType);
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }

        if ($generateUrlStr == '') {
            try {
                $generateUrlStr = $this->urlGenerator->generate($pageId, $parameters, $referenceType);
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
            }
        }

        return $generateUrlStr;
    }

    public function getActiveMenu(): array
    {
        $this->menuService->load();

        return $this->menuService->getActiveMenuItem() ?? [];
    }
}
