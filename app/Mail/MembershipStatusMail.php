<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MembershipStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $event; // activation|renewal|canceled|updated

    public function __construct(User $user, string $event)
    {
        $this->user = $user;
        $this->event = $event;
    }

    public function build(): self
    {
        $subject = match ($this->event) {
            'activation' => 'Your MADE Membership is Active',
            'renewal' => 'Your MADE Membership Renewed',
            'canceled' => 'Your MADE Membership was Canceled',
            default => 'Your MADE Membership Update',
        };

        return $this->subject($subject)
            ->view('emails.membership_status')
            ->with([
                'user' => $this->user,
                'event' => $this->event,
            ]);
    }
}
