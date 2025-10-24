<?php

namespace App\Consoles\Http\Controllers;

use App\Consoles\Domain\QualityConsoleService;
use App\Consoles\Http\Requests\QualityActionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QualityController extends Controller
{
    public function index(QualityConsoleService $service): View
    {
        $this->authorize('viewQualityConsole');

        $state = $service->summary(currentCompanyId());

        return view('consoles::admin.quality.index', [
            'state' => $state,
        ]);
    }

    public function record(QualityActionRequest $request, QualityConsoleService $service): RedirectResponse
    {
        $this->authorize('viewQualityConsole');

        $data = $request->validated();
        $companyId = currentCompanyId();

        foreach ($data['selection'] as $selection) {
            $service->record(
                $companyId,
                $selection['subject_type'],
                (int) $selection['subject_id'],
                $selection['direction'],
                $data['result'],
                $data['notes'] ?? null,
            );
        }

        return back()->with('status', __('Kalite kaydı güncellendi.'));
    }
}
