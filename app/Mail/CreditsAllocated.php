<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreditsAllocated extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public int $amount;
    public string $creditLabel;
    public int $newBalance;
    public ?string $note;
    public string $allocatedBy;

    public function __construct(
        User $user,
        int $amount,
        string $creditLabel,
        int $newBalance,
        ?string $note = null,
        string $allocatedBy = 'Admin'
    ) {
        $this->user = $user;
        $this->amount = $amount;
        $this->creditLabel = $creditLabel;
        $this->newBalance = $newBalance;
        $this->note = $note;
        $this->allocatedBy = $allocatedBy;
    }

    public function build(): self
    {
        return $this
            ->subject('Credits Added to Your Account')
            ->view('emails.credits_allocated')
            ->with([
                'user' => $this->user,
                'amount' => $this->amount,
                'creditLabel' => $this->creditLabel,
                'newBalance' => $this->newBalance,
                'note' => $this->note,
                'allocatedBy' => $this->allocatedBy,
            ]);
    }
}
