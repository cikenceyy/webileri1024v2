<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Note;
use App\Modules\Marketing\Http\Requests\StoreNoteRequest;
use Illuminate\Http\RedirectResponse;

class NoteController extends \App\Http\Controllers\Controller
{
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['created_by'] = $request->user()?->id;

        Note::create($data);

        return back()->with('status', __('Note saved.'));
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('status', __('Note removed.'));
    }
}
