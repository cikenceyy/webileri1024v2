<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ __('Contacts') }}</h6>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#contact-form">{{ __('Add Contact') }}</button>
</div>

<div class="collapse mb-3" id="contact-form">
    <form method="post" action="{{ route('admin.marketing.contacts.store', $customer) }}" class="card card-body gap-3">
        @csrf
        <div class="row g-3">
            <div class="col-md-6"><x-ui-input name="name" :label="__('Name')" required /></div>
            <div class="col-md-6"><x-ui-input name="title" :label="__('Title')" /></div>
            <div class="col-md-6"><x-ui-input name="email" type="email" :label="__('Email')" /></div>
            <div class="col-md-6"><x-ui-input name="phone" :label="__('Phone')" /></div>
            <div class="col-12"><x-ui-checkbox name="is_primary" :label="__('Primary contact')" value="1" /></div>
        </div>
        <div class="d-flex gap-2">
            <x-ui-button type="submit">{{ __('Save') }}</x-ui-button>
            <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#contact-form">{{ __('Cancel') }}</button>
        </div>
    </form>
</div>

<ul class="list-group list-group-flush">
    @forelse($customer->contacts as $contact)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">{{ $contact->name }} @if($contact->is_primary)<span class="badge bg-success ms-2">{{ __('Primary') }}</span>@endif</div>
                <div class="text-muted small">{{ $contact->title }} · {{ $contact->email }} · {{ $contact->phone }}</div>
            </div>
            <div class="d-flex gap-2">
                <form method="post" action="{{ route('admin.marketing.contacts.update', $contact) }}" class="d-inline-flex gap-2">
                    @csrf
                    @method('put')
                    <input type="hidden" name="is_primary" value="1">
                    <x-ui-button type="submit" variant="secondary" size="sm">{{ __('Set Primary') }}</x-ui-button>
                </form>
                <form method="post" action="{{ route('admin.marketing.contacts.destroy', $contact) }}">
                    @csrf
                    @method('delete')
                    <x-ui-button type="submit" variant="danger" size="sm">{{ __('Remove') }}</x-ui-button>
                </form>
            </div>
        </li>
    @empty
        <li class="list-group-item text-muted">{{ __('No contacts recorded.') }}</li>
    @endforelse
</ul>
