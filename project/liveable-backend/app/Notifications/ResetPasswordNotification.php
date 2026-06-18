<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $url;
    public $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct($url, $userName)
    {
        $this->url = $url;
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->greeting("Olá, $this->userName !")
            ->line('Você solicitou a redefinição de sua senha.')
            ->action('Redefinir Senha', $this->url)
            ->line('O link expira em 60 minutos.')
            ->line('Se não foi você, ignore este email.');
    }
}
