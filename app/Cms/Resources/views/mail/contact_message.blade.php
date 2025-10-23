<table style="width:100%; font-family:Arial, sans-serif;">
    <tr>
        <td style="padding:16px; background:#f6f6f6;">
            <h2 style="margin:0 0 12px 0;">Yeni İletişim Mesajı</h2>
            <p style="margin:0 0 8px 0;"><strong>Ad Soyad:</strong> {{ $messageModel->name }}</p>
            <p style="margin:0 0 8px 0;"><strong>E-posta:</strong> {{ $messageModel->email }}</p>
            <p style="margin:0 0 8px 0;"><strong>Konu:</strong> {{ $messageModel->subject }}</p>
            <p style="margin:16px 0; white-space:pre-line;">{{ $messageModel->message }}</p>
            <p style="margin:0 0 8px 0; font-size:12px; color:#555;">IP: {{ $messageModel->ip }} | User Agent: {{ $messageModel->user_agent }}</p>
            <p style="margin:16px 0 0 0;"><a href="{{ route('cms.admin.messages.show', $messageModel) }}">Panelde görüntüle</a></p>
        </td>
    </tr>
</table>
