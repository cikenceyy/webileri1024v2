@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">İletişim Mesajları</h1>
        <div class="mb-3">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="unread" value="1" id="unreadCheck" @checked(request('unread'))>
                        <label class="form-check-label" for="unreadCheck">Sadece okunmamış</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Filtrele</button>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Konu</th>
                            <th>Tarih</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr @class(['fw-bold' => !$message->is_read])>
                                <td>{{ $message->name }}</td>
                                <td>{{ $message->email }}</td>
                                <td>{{ $message->subject }}</td>
                                <td>{{ $message->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end"><a href="{{ route('cms.admin.messages.show', $message) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $messages->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
