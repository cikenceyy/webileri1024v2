<?php

namespace App\Cms\Http\Controllers\Admin;

use App\Cms\Http\Controllers\Admin\Concerns\HandlesCmsPayload;
use App\Cms\Support\CmsRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PageController extends Controller
{
    use HandlesCmsPayload;

    public function __construct(protected CmsRepository $repository)
    {
    }

    public function index()
    {
        return view('admin.cms.index', [
            'pages' => config('cms.pages'),
        ]);
    }

    public function edit(string $page)
    {
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        return view('admin.cms.edit', [
            'pageKey' => $page,
            'pageConfig' => $pageConfig,
            'content' => [
                'tr' => $this->repository->read($page, 'tr'),
                'en' => $this->repository->read($page, 'en'),
            ],
            'seo' => [
                'tr' => $this->repository->seo($page, 'tr'),
                'en' => $this->repository->seo($page, 'en'),
            ],
            'scripts' => [
                'tr' => $this->repository->scripts($page, 'tr'),
                'en' => $this->repository->scripts($page, 'en'),
            ],
            'emails' => $this->repository->emails(),
        ]);
    }

    public function update(string $page, Request $request)
    {
        $pageConfig = config('cms.pages.' . $page);
        abort_unless($pageConfig, 404);

        $this->validatePageRequest($pageConfig, $request);

        $payload = $this->normalisePagePayload($pageConfig, $request);

        foreach (['tr', 'en'] as $locale) {
            $this->repository->write($page, $locale, [
                'blocks' => $payload['content'][$locale] ?? [],
                'seo' => $payload['seo'][$locale] ?? [],
                'scripts' => $payload['scripts'][$locale] ?? ['header' => null, 'footer' => null],
            ]);
        }

        $this->repository->updateEmails($payload['emails']);

        return redirect()->route('cms.admin.pages.edit', $page)->with('status', __('Saved successfully.'));
    }
}
