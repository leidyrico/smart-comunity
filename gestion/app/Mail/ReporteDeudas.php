<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteDeudas extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;
    public $filtros;
    public $apartamento;

    /**
     * Create a new message instance.
     */
    public function __construct($datos, $filtros, $apartamento)
    {
        $this->datos = $datos;
        $this->filtros = $filtros;
        $this->apartamento = $apartamento;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Reporte de Deudas';
        
        if ($this->apartamento) {
            $subject .= ' - Apartamento ' . $this->apartamento->numero;
        }
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-deudas',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}