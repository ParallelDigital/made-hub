<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PackageCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $type)
    {
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your MADE package code')
            ->view('emails.package-code');
    }
}
