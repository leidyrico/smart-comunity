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
            // Obtener la ruta completa del archivo
            $rutaArchivo = $this->recibo->archivo_adjunto;
            
            // Extraer el nombre original del archivo (después del timestamp_)
            $nombreArchivo = basename($rutaArchivo);
            $partesNombre = explode('_', $nombreArchivo, 2);
            $nombreOriginal = count($partesNombre) > 1 ? $partesNombre[1] : $nombreArchivo;
            
            // Determinar el tipo MIME basado en la extensión del archivo original
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $mimeType = match($extension) {
                'pdf' => 'application/pdf',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls' => 'application/vnd.ms-excel',
                default => 'application/octet-stream'
            };
            
            // Crear el nombre del archivo adjunto manteniendo la extensión original
            $nombreAdjunto = 'Recibo_' . $this->recibo->numero_recibo . '.' . $extension;
            
            $attachments[] = Attachment::fromStorageDisk('public', $rutaArchivo)
                ->as($nombreAdjunto)
                ->withMime($mimeType);
        }
        
        return $attachments;
    }
}