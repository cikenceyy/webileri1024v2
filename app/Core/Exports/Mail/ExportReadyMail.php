<?php

namespace App\Core\Exports\Mail;

use App\Core\Exports\Models\TableExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Export tamamlandığında kullanıcıya bilgilendirme gönderen mailable sınıfı.
 */
class ExportReadyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public TableExport $export)
    {
    }

    public function build(): self
    {
        return $this->subject(__('Veri dışa aktarma hazır'))
            ->view('mail.exports.ready', [
                'export' => $this->export,
            ]);
    }
}
