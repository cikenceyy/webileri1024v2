<div class="mb-3">
    <form method="post" action="{{ route('admin.marketing.attachments.store') }}" class="card card-body gap-3">
        @csrf
        <input type="hidden" name="related_type" value="{{ $relatedType }}">
        <input type="hidden" name="related_id" value="{{ $relatedId }}">
        <x-ui-input name="media_id" :label="__('Media ID')" required />
        <x-ui-button type="submit">{{ __('Attach') }}</x-ui-button>
    </form>
</div>

<ul class="list-group list-group-flush">
    @forelse($attachments as $attachment)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                {{ $attachment->media->original_name ?? __('File') }}
                <span class="text-muted small">{{ optional($attachment->created_at)->format('d.m.Y H:i') }}</span>
            </div>
            <form method="post" action="{{ route('admin.marketing.attachments.destroy', $attachment) }}">
                @csrf
                @method('delete')
                <x-ui-button type="submit" variant="danger" size="sm">{{ __('Remove') }}</x-ui-button>
            </form>
        </li>
    @empty
        <li class="list-group-item text-muted">{{ __('No attachments yet.') }}</li>
    @endforelse
</ul>
