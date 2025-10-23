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
            $query->where(function ($builder) {
                $builder->where('is_read', false)->orWhereNull('read_at');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $messages = $query->paginate(20);

        return view('cms::admin.messages.index', [
            'messages' => $messages,
            'filters' => $request->only(['unread', 'status', 'q', 'date_from', 'date_to']),
        ]);
    }

    public function show(ContactMessage $message)
    {
        abort_if($message->company_id !== $this->repository->companyId(), 403);

        if (!$message->is_read) {
            $message->forceFill([
                'is_read' => true,
                'read_at' => $message->read_at ?? now(),
                'status' => $message->status === 'responded' ? 'responded' : 'read',
            ])->save();
        }

        return view('cms::admin.messages.show', [
            'message' => $message,
        ]);
    }

    public function update(ContactMessage $message, Request $request)
    {
        abort_if($message->company_id !== $this->repository->companyId(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:new,read,responded'],
        ]);

        $status = $validated['status'];

        $attributes = [
            'status' => $status,
            'is_read' => true,
            'read_at' => $message->read_at ?? now(),
        ];

        if ($status === 'responded') {
            $attributes['responded_at'] = $message->responded_at ?? now();
        }

        if ($status === 'new') {
            $attributes['is_read'] = false;
            $attributes['read_at'] = null;
            $attributes['responded_at'] = null;
        }

        $message->forceFill($attributes)->save();

        return redirect()->back()->with('status', __('Message status updated.'));
    }
}
