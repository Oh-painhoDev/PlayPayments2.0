<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Gerar URL completa para reset de senha
        $email = $notifiable->getEmailForPasswordReset();
        
        // Garantir que a URL seja absoluta
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
        ], false)) . '?email=' . urlencode($email);

        // Log para debug (remover em produção se necessário)
        \Log::info('Gerando URL de reset de senha', [
            'email' => $email,
            'token' => substr($this->token, 0, 10) . '...',
            'url' => $resetUrl
        ]);

        return (new MailMessage)
            ->subject('Recuperação de Senha - ' . config('app.name'))
            ->greeting('Olá!')
            ->line('Você está recebendo este email porque recebemos uma solicitação de recuperação de senha para sua conta.')
            ->action('Redefinir Senha', $resetUrl)
            ->line('Este link de recuperação expira em 60 minutos.')
            ->line('Se você não solicitou uma recuperação de senha, nenhuma ação é necessária.')
            ->salutation('Atenciosamente, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
