<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserInvitationNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]));

        return (new MailMessage)
            ->subject('Welkom bij ' . config('app.name'))
            ->greeting('Hallo ' . $notifiable->name . ',')
            ->line('Je bent uitgenodigd om een account aan te maken op ons platform.')
            ->line('Klik op onderstaande knop om je wachtwoord in te stellen en toegang te krijgen tot je account.')
            ->action('Stel je wachtwoord in', $url)
            ->line('Deze link verloopt binnen 60 minuten.')
            ->line('Als je dit niet verwachtte, kun je deze e-mail negeren.');
    }
}
