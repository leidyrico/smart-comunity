<?php

namespace App\Services;

use App\Models\Apartamento;
use App\Mail\NuevoRecibo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

// Incluir configuración de timeout para evitar errores de tiempo de ejecución
require_once __DIR__ . '/../../config_timeout.php';

class EmailMasivoService
{
    /**
     * Tamaño máximo de lote para envío SMTP
     */
    private const TAMANO_LOTE = 50;

    /**
     * Obtiene todos los emails únicos y válidos de los apartamentos
     *
     * @return array
     */
    public function obtenerEmailsUnicos(): array
    {
        $apartamentos = Apartamento::whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $emails = [];
        
        foreach ($apartamentos as $apartamento) {
            $email = trim(strtolower($apartamento->email));
            
            // Validar formato de email
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        // Eliminar duplicados y reindexar
        return array_values(array_unique($emails));
    }

    /**
     * Obtiene estadísticas de emails en la base de datos
     *
     * @return array
     */
    public function obtenerEstadisticasEmails(): array
    {
        $totalApartamentos = Apartamento::count();
        $apartamentosConEmail = Apartamento::whereNotNull('email')
            ->where('email', '!=', '')
            ->count();
        
        $emailsUnicos = $this->obtenerEmailsUnicos();
        
        return [
            'total_apartamentos' => $totalApartamentos,
            'apartamentos_con_email' => $apartamentosConEmail,
            'emails_unicos_validos' => count($emailsUnicos),
            'apartamentos_sin_email' => $totalApartamentos - $apartamentosConEmail
        ];
    }

    /**
     * Envía un correo masivo optimizado para un recibo
     *
     * @param \App\Models\ReciboGastoComun $recibo
     * @param string|null $archivoAdjunto Ruta del archivo adjunto del recibo
     * @return array
     */
    public function enviarCorreoMasivo($recibo, $archivoAdjunto = null): array
    {
        try {
            $emails = $this->obtenerEmailsUnicos();
            
            if (empty($emails)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron emails válidos para enviar',
                    'total_emails' => 0
                ];
            }

            Log::info('Iniciando envío masivo de correos', [
                'recibo_numero' => $recibo->numero_recibo,
                'total_emails' => count($emails),
                'archivo_adjunto' => $archivoAdjunto ? 'Sí' : 'No'
            ]);

            $this->enviarCorreoUnico($emails, $recibo, $archivoAdjunto);

            Log::info('Envío masivo completado exitosamente', [
                'recibo_numero' => $recibo->numero_recibo,
                'emails_enviados' => count($emails)
            ]);

            return [
                'success' => true,
                'message' => 'Correo enviado exitosamente a ' . count($emails) . ' destinatarios' . ($archivoAdjunto ? ' con archivo adjunto' : ''),
                'total_emails' => count($emails),
                'emails' => $emails
            ];

        } catch (Exception $e) {
            Log::error('Error en envío masivo de correos', [
                'recibo_numero' => $recibo->numero_recibo ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar correos: ' . $e->getMessage(),
                'total_emails' => 0
            ];
        }
    }

    /**
     * Envía un correo único a múltiples destinatarios usando BCC
     *
     * @param array $emails
     * @param \App\Models\ReciboGastoComun $recibo
     * @param string|null $archivoAdjunto Ruta del archivo adjunto del recibo
     * @return void
     */
    private function enviarCorreoUnico(array $emails, $recibo, $archivoAdjunto = null): void
    {
        // Dividir emails en lotes para respetar límites SMTP
        $lotes = array_chunk($emails, self::TAMANO_LOTE);
        
        foreach ($lotes as $indice => $lote) {
            try {
                Log::info('Enviando lote ' . ($indice + 1) . ' de ' . count($lotes));
                
                // Crear instancia del mail
                $mailInstance = new NuevoRecibo($recibo, 'Propietario');
                
                // Nota: El archivo adjunto se maneja automáticamente en NuevoRecibo::attachments()
                // No es necesario adjuntarlo aquí para evitar duplicación
                
                Mail::to($lote[0]) // Primer destinatario como TO
                    ->bcc(array_slice($lote, 1)) // Resto como BCC para privacidad
                    ->send($mailInstance);
                    
                Log::info('Lote ' . ($indice + 1) . ' enviado exitosamente', [
                    'archivo_adjunto' => $archivoAdjunto ? 'Sí' : 'No'
                ]);
                
                // Pequeña pausa entre lotes para evitar sobrecarga del servidor SMTP
                if ($indice < count($lotes) - 1) {
                    usleep(500000); // 0.5 segundos
                }
                
            } catch (Exception $e) {
                Log::error('Error enviando lote ' . ($indice + 1), [
                    'error' => $e->getMessage(),
                    'lote_size' => count($lote)
                ]);
                throw $e;
            }
        }
    }
}