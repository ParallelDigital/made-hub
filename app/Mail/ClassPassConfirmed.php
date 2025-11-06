<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassPassConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $passType; // 'unlimited' or 'credits'
    public ?int $credits;    // number of credits when passType='credits'
    public ?\Carbon\CarbonInterface $expiresAt;
    public string $source;
    public bool $isNewAccount;
    public ?string $password;
    public bool $isMember;

    public function __construct(User $user, string $passType, ?int $credits, ?\Carbon\CarbonInterface $expiresAt, string $source = 'Stripe Purchase', bool $isNewAccount = false, ?string $password = null, bool $isMember = false)
    {
        $this->user = $user;
        $this->passType = $passType;
        $this->credits = $credits;
        $this->expiresAt = $expiresAt;
        $this->source = $source;
        $this->isNewAccount = $isNewAccount;
        $this->password = $password;
        $this->isMember = $isMember;
    }

    public function build(): self
    {
        $subject = $this->passType === 'unlimited'
            ? 'Your Unlimited Class Pass is Active'
            : 'Your Class Pass Credits Are Ready';

        return $this
            ->subject($subject)
            ->view('emails.class-pass-confirmed')
            ->with([
                'user' => $this->user,
                'passType' => $this->passType,
                'credits' => $this->credits,
                'expiresAt' => $this->expiresAt,
                'source' => $this->source,
                'isNewAccount' => $this->isNewAccount,
                'password' => $this->password,
                'isMember' => $this->isMember,
            ]);
    }
}
