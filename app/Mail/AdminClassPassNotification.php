<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminClassPassNotification extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $passType; // 'unlimited' or 'credits'
    public ?int $credits;    // number of credits when passType='credits'
    public ?\Carbon\CarbonInterface $expiresAt;
    public string $source;

    public function __construct(User $user, string $passType, ?int $credits, ?\Carbon\CarbonInterface $expiresAt, string $source = 'Stripe Purchase')
    {
        $this->user = $user;
        $this->passType = $passType;
        $this->credits = $credits;
        $this->expiresAt = $expiresAt;
        $this->source = $source;
    }

    public function build(): self
    {
        $subject = $this->passType === 'unlimited'
            ? 'New Unlimited Class Pass Purchased'
            : 'New Class Pass Credits Purchased';

        return $this
            ->subject($subject)
            ->view('emails.admin.class-pass-notification')
            ->with([
                'user' => $this->user,
                'passType' => $this->passType,
                'credits' => $this->credits,
                'expiresAt' => $this->expiresAt,
                'source' => $this->source,
            ]);
    }
}
