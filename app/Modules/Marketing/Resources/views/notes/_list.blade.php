<div class="mb-3">
    <form method="post" action="{{ route('admin.marketing.notes.store') }}" class="card card-body gap-3">
        @csrf
        <input type="hidden" name="related_type" value="{{ $relatedType }}">
        <input type="hidden" name="related_id" value="{{ $relatedId }}">
        <x-ui.textarea name="body" :label="__('Add note')" rows="3" required />
        <x-ui.button type="submit">{{ __('Save Note') }}</x-ui.button>
    </form>
</div>

<ul class="list-group list-group-flush">
    @forelse($notes as $note)
        <li class="list-group-item d-flex justify-content-between align-items-start">
            <div>
                <div class="text-muted small">{{ $note->created_at->diffForHumans() }}</div>
                <div>{{ $note->body }}</div>
            </div>
            <form method="post" action="{{ route('admin.marketing.notes.destroy', $note) }}">
                @csrf
                @method('delete')
                <x-ui.button type="submit" variant="danger" size="sm">{{ __('Remove') }}</x-ui.button>
            </form>
        </li>
    @empty
        <li class="list-group-item text-muted">{{ __('No notes yet.') }}</li>
    @endforelse
</ul>
