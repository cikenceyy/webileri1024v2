<?php

namespace App\Cms\Http\Controllers\Admin;

use App\Cms\Http\Controllers\Admin\Concerns\HandlesCmsPayload;
use App\Cms\Jobs\WarmPageCache;
use App\Cms\Support\AuditLogger;
use App\Cms\Support\CmsRepository;
use App\Cms\Support\PreviewStore;
use App\Cms\Support\Seo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditorController extends Controller
{
    use HandlesCmsPayload;

    public function __construct(
        protected CmsRepository $repository,
        protected Seo $seo,
        protected PreviewStore $preview,
        protected AuditLogger $audit
    ) {
    }

    public function index(Request $request)
    {
        $page = $this->resolvePage($request);
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        $activeLocale = $this->resolveLocale($request);
        $token = $this->preview->issueToken();

        $content = [
            'tr' => $this->repository->read($page, 'tr'),
            'en' => $this->repository->read($page, 'en'),
        ];

        $seoData = [
            'tr' => $this->repository->seo($page, 'tr'),
            'en' => $this->repository->seo($page, 'en'),
        ];

        $scripts = [
            'tr' => $this->repository->scripts($page, 'tr'),
            'en' => $this->repository->scripts($page, 'en'),
        ];

        $previewUrl = $this->previewUrl($page, $activeLocale, $token, $request);

        return view('cms::admin.cms.editor', [
            'pageKey' => $page,
            'pageConfig' => $pageConfig,
            'content' => $content,
            'seoData' => $seoData,
            'scripts' => $scripts,
            'emails' => $this->repository->emails(),
            'previewToken' => $token,
            'previewUrl' => $previewUrl,
            'activeLocale' => $activeLocale,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $page = $request->input('page');
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        $this->validatePageRequest($pageConfig, $request);
        $payload = $this->normalisePagePayload($pageConfig, $request);

        foreach (['tr', 'en'] as $locale) {
            $before = $this->repository->read($page, $locale);
            $data = [
                'blocks' => Arr::get($payload, "content.$locale", []),
                'seo' => Arr::get($payload, "seo.$locale", []),
                'scripts' => Arr::get($payload, "scripts.$locale", ['header' => null, 'footer' => null]),
            ];

            $this->audit->log($page, $locale, $before, $data);
            $this->repository->write($page, $locale, $data);
        }

        $this->repository->updateEmails($payload['emails']);

        if ($token = $this->previewTokenFromRequest($request)) {
            $this->preview->clear($token, $page);
        }

        if (config('cms.cache.warm_pages', true)) {
            foreach ($this->warmableUrls($page) as $locale => $url) {
                WarmPageCache::dispatchAfterResponse($url, $locale);
            }
        }

        return response()->json([
            'status' => 'saved',
            'message' => __('Content saved successfully.'),
        ]);
    }

    protected function resolvePage(Request $request): string
    {
        if ($request->filled('page')) {
            return $request->query('page');
        }

        $url = $request->query('url', '/');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '/', '/');

        return match (true) {
            $path === '' => 'home',
            $path === 'en' => 'home',
            str_starts_with($path, 'en/') && Str::after($path, 'en/') === '' => 'home',
            $path === 'kurumsal' || $path === 'en/corporate' => 'corporate',
            $path === 'iletisim' || $path === 'en/contact' => 'contact',
            $path === 'bilgi/kvkk' || $path === 'en/info/kvkk' => 'kvkk',
            $path === 'kataloglar' || $path === 'en/catalogs' => 'catalogs',
            $path === 'urunler' || $path === 'en/products' => 'products',
            default => 'home',
        };
    }

    protected function resolveLocale(Request $request): string
    {
        if ($request->filled('locale') && in_array($request->query('locale'), ['tr', 'en'], true)) {
            return $request->query('locale');
        }

        $url = $request->query('url', '/');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '/', '/');

        return str_starts_with($path, 'en') ? 'en' : 'tr';
    }

    protected function previewUrl(string $page, string $locale, string $token, Request $request): string
    {
        $base = match ($page) {
            'home' => $locale === 'en' ? route('cms.en.home') : route('cms.home'),
            'corporate' => $locale === 'en' ? route('cms.en.corporate') : route('cms.corporate'),
            'contact' => $locale === 'en' ? route('cms.en.contact') : route('cms.contact'),
            'kvkk' => $locale === 'en' ? route('cms.en.kvkk') : route('cms.kvkk'),
            'catalogs' => $locale === 'en' ? route('cms.en.catalogs') : route('cms.catalogs'),
            'products' => $locale === 'en' ? route('cms.en.products') : route('cms.products'),
            default => $locale === 'en' ? route('cms.en.home') : route('cms.home'),
        };

        $query = ['preview_token' => $token];
        if ($request->filled('preview')) {
            $query['preview'] = $request->query('preview');
        }

        return $base . '?' . http_build_query($query);
    }

    protected function previewTokenFromRequest(Request $request): ?string
    {
        return $request->header('X-CMS-Preview-Token')
            ?? $request->input('preview_token')
            ?? $this->preview->token();
    }

    protected function warmableUrls(string $page): array
    {
        return array_filter([
            'tr' => $this->seo->for($page, [], 'tr')['canonical'] ?? null,
            'en' => $this->seo->for($page, [], 'en')['canonical'] ?? null,
        ]);
    }
}
