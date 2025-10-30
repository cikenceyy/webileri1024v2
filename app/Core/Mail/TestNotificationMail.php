<?php

namespace App\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Ayar panelindeki deneme gönderimleri için basit mailable.
 */
class TestNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly string $recipientName)
    {
        $this->onQueue('emails');
    }

    public function build(): self
    {
        return $this->subject('Webileri 1024 - Test E-postası')
            ->view('mail.test-notification', [
                'name' => $this->recipientName,
            ]);
    }
}
