<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Activity;
use App\Modules\Marketing\Http\Requests\StoreActivityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query()->latest();

        if ($relatedType = $request->query('related_type')) {
            $query->where('related_type', $relatedType);
        }

        if ($relatedId = $request->query('related_id')) {
            $query->where('related_id', $relatedId);
        }

        return view('marketing::activities.index', [
            'activities' => $query->paginate(15)->withQueryString(),
            'embedded' => false,
        ]);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        Activity::create($data);

        return back()->with('status', __('Activity saved.'));
    }

    public function update(StoreActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $activity->update($request->validated());

        return back()->with('status', __('Activity updated.'));
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return back()->with('status', __('Activity removed.'));
    }
}
