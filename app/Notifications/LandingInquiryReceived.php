<?php

namespace App\Notifications;

use App\Models\LandingPage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LandingInquiryReceived extends Notification
{
    public function __construct(public LandingPage $landing) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva consulta · '.$this->landing->name)
            ->greeting('Una nueva historia comienza.')
            ->line('Se ha guardado una nueva solicitud en la landing '.$this->landing->name.'.')
            ->line('Revisa la fecha, los datos de la pareja y su campaña de origen desde el administrador.')
            ->action('Ver consultas', route('admin.landings.leads', $this->landing))
            ->line('El envío del formulario no confirma disponibilidad ni reserva.');
    }
}
