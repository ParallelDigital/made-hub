<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $remainingClasses;

    /**
     * Create a new message instance.
     */
    public function __construct($member, $remainingClasses = 5)
    {
        $this->member = $member;
        $this->remainingClasses = $remainingClasses;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to Made Hub!')
                    ->view('emails.member-welcome')
                    ->with([
                        'member' => $this->member,
                        'remainingClasses' => $this->remainingClasses,
                    ]);
    }
}
