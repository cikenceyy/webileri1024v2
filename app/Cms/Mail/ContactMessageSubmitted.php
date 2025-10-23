<?php

namespace App\Cms\Mail;

use App\Cms\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageSubmitted extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
        $this->onQueue('emails');
    }

    public function build(): self
    {
        return $this->subject('[Site İletişim] ' . $this->contactMessage->subject . ' – ' . $this->contactMessage->name)
            ->view('cms::mail.contact_message')
            ->with([
                'messageModel' => $this->contactMessage,
            ]);
    }
}
