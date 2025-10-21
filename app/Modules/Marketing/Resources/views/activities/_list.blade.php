<ul class="list-group list-group-flush">
    @forelse($activities as $activity)
        <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">{{ $activity->subject }} <span class="badge bg-light text-dark ms-2">{{ ucfirst($activity->type) }}</span></div>
                    <div class="text-muted small">{{ optional($activity->due_at)->format('d.m.Y H:i') }}</div>
                </div>
                @can('delete', $activity)
                    <form method="post" action="{{ route('admin.marketing.activities.destroy', $activity) }}">
                        @csrf
                        @method('delete')
                        <x-ui.button type="submit" size="sm" variant="danger">{{ __('Remove') }}</x-ui.button>
                    </form>
                @endcan
            </div>
            @if($activity->notes)
                <div class="mt-2 text-muted">{{ $activity->notes }}</div>
            @endif
        </li>
    @empty
        <li class="list-group-item text-muted">{{ __('No activities yet.') }}</li>
    @endforelse
</ul>
