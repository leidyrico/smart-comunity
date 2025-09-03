<?php

namespace App\Mail;

use App\Models\ReciboGastoComun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NuevoRecibo extends Mailable
{
    use Queueable, SerializesModels;

    public $recibo;
    public $propietario;

    /**
     * Create a new message instance.
     */
    public function __construct(ReciboGastoComun $recibo, $propietario = null)
    {
        $this->recibo = $recibo;
        $this->propietario = $propietario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Emisión de nuevo recibo de gasto común – Residencias Alfa',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nuevo-recibo',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // Adjuntar archivo si existe
        if ($this->recibo->archivo_adjunto && Storage::disk('public')->exists($this->recibo->archivo_adjunto)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->recibo->archivo_adjunto)
                ->as('Recibo_' . $this->recibo->numero_recibo . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}