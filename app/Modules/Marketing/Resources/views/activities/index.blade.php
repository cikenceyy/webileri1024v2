@if(!($embedded ?? false))
    <x-ui.page-header :title="__('Activities')" />
@endif

@if(!($embedded ?? false))
    <form method="get" class="card mb-3 p-3">
        <div class="row g-3">
            <div class="col-md-6"><x-ui.input name="related_type" :label="__('Related Type')" :value="request('related_type')" /></div>
            <div class="col-md-6"><x-ui.input name="related_id" :label="__('Related ID')" :value="request('related_id')" /></div>
            <div class="col-12"><x-ui.button type="submit">{{ __('Filter') }}</x-ui.button></div>
        </div>
    </form>
@endif

@if(($embedded ?? false) === false)
    <x-ui.card>
        @include('marketing::activities._list', ['activities' => $activities])
    </x-ui.card>
@else
    @include('marketing::activities._list', ['activities' => $activities])
@endif
