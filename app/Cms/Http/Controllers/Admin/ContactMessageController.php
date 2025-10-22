<?php

namespace App\Cms\Http\Controllers\Admin;

use App\Cms\Models\ContactMessage;
use App\Cms\Support\CmsRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactMessageController extends Controller
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $query = ContactMessage::query()
            ->where('company_id', $this->repository->companyId())
            ->latest();

        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date('date'));
        }

        $messages = $query->paginate(20);

        return view('cms::admin.messages.index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $message)
    {
        abort_if($message->company_id !== $this->repository->companyId(), 403);

        if (!$message->is_read) {
            $message->forceFill(['is_read' => true])->save();
        }

        return view('cms::admin.messages.show', [
            'message' => $message,
        ]);
    }
}
