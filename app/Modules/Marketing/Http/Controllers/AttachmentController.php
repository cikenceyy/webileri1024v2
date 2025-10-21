<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Attachment;
use App\Modules\Marketing\Http\Requests\StoreAttachmentRequest;
use Illuminate\Http\RedirectResponse;

class AttachmentController extends \App\Http\Controllers\Controller
{
    public function store(StoreAttachmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['uploaded_by'] = $request->user()?->id;

        $media = \App\Modules\Drive\Domain\Models\Media::where('id', $data['media_id'])
            ->where('company_id', $data['company_id'])
            ->firstOrFail();

        Attachment::create($data + ['media_id' => $media->id]);

        return back()->with('status', __('Attachment saved.'));
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $attachment->delete();

        return back()->with('status', __('Attachment removed.'));
    }
}
