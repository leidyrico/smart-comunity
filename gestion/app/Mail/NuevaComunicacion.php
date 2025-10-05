<?php

namespace App\Mail;

use App\Models\Acta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class NuevaComunicacion extends Mailable
{
    use Queueable, SerializesModels;

    public $acta;
    public $propietario;

    /**
     * Create a new message instance.
     */
    public function __construct(Acta $acta, $propietario = null)
    {
        $this->acta = $acta;
        $this->propietario = $propietario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva comunicación – Residencias Alfa',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva-comunicacion',
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
        
        // Adjuntar archivo si existe (las actas guardan el archivo en base64)
        if ($this->acta->archivo_contenido && $this->acta->archivo_nombre) {
            // Crear archivo temporal desde base64
            $contenidoDecodificado = base64_decode($this->acta->archivo_contenido);
            $nombreTemporal = tempnam(sys_get_temp_dir(), 'acta_');
            file_put_contents($nombreTemporal, $contenidoDecodificado);
            
            // Extraer extensión del nombre original
            $extension = strtolower(pathinfo($this->acta->archivo_nombre, PATHINFO_EXTENSION));
            
            // Determinar el tipo MIME basado en la extensión
            $mimeType = match($extension) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => 'application/octet-stream'
            };
            
            // Crear el nombre del archivo adjunto
            $nombreAdjunto = $this->acta->tipo_documento . '_' . $this->acta->nro_doc . '.' . $extension;
            
            $attachments[] = Attachment::fromPath($nombreTemporal)
                ->as($nombreAdjunto)
                ->withMime($mimeType);
        }
        
        return $attachments;
    }
}