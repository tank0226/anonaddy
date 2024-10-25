<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class TokenExpiringSoon extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $user;

    protected $recipient;

    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->recipient = $user->defaultRecipient;
        $this->token = $user->tokens()->whereDate('expires_at', now()->addWeek())->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Your '.config('app.name').' API key expires soon')
            ->markdown('mail.token_expiring_soon', [
                'user' => $this->user,
                'userId' => $this->user->id,
                'recipientId' => $this->user->default_recipient_id,
                'emailType' => 'TES',
                'fingerprint' => $this->recipient->should_encrypt ? $this->recipient->fingerprint : null,
                'tokenName' => $this->token?->name,
            ])
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()
                    ->addTextHeader('Feedback-ID', 'TES:anonaddy');
            });
    }
}
