<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateImportErrorReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:error-report {--days=7 : Número de días hacia atrás para buscar errores}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un reporte detallado de errores de importación de los últimos días';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Generando reporte de errores de importación de los últimos {$days} días...");

        try {
            // Leer el archivo de log de Laravel
            $logPath = storage_path('logs/laravel.log');
            
            if (!file_exists($logPath)) {
                $this->error('No se encontró el archivo de log de Laravel.');
                return 1;
            }

            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            
            $importErrors = [];
            $currentDate = Carbon::now();
            $cutoffDate = $currentDate->subDays($days);

            foreach ($lines as $line) {
                // Buscar líneas que contengan errores de importación
                if (strpos($line, 'Error importando') !== false || 
                    strpos($line, 'importCompleto') !== false ||
                    strpos($line, 'importación Excel') !== false) {
                    
                    // Extraer la fecha del log
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                        $logDate = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
                        
                        if ($logDate->gte($cutoffDate)) {
                            $importErrors[] = [
                                'timestamp' => $logDate->format('Y-m-d H:i:s'),
                                'message' => trim($line)
                            ];
                        }
                    }
                }
            }

            if (empty($importErrors)) {
                $this->info('No se encontraron errores de importación en el período especificado.');
                return 0;
            }

            // Generar el reporte
            $reportContent = $this->generateReport($importErrors, $days);
            
            // Guardar el reporte
            $reportFileName = 'import_error_report_' . date('Y-m-d_H-i-s') . '.txt';
            $reportPath = storage_path('app/reports/' . $reportFileName);
            
            // Crear directorio si no existe
            if (!is_dir(dirname($reportPath))) {
                mkdir(dirname($reportPath), 0755, true);
            }
            
            file_put_contents($reportPath, $reportContent);
            
            $this->info("Reporte generado exitosamente: {$reportPath}");
            $this->info("Total de errores encontrados: " . count($importErrors));
            
            // Mostrar resumen en consola
            $this->displaySummary($importErrors);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error al generar el reporte: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generar el contenido del reporte
     */
    private function generateReport($errors, $days)
    {
        $report = "REPORTE DE ERRORES DE IMPORTACIÓN\n";
        $report .= "Generado: " . date('Y-m-d H:i:s') . "\n";
        $report .= "Período: Últimos {$days} días\n";
        $report .= "Total de errores: " . count($errors) . "\n";
        $report .= str_repeat('=', 80) . "\n\n";

        // Agrupar errores por tipo
        $errorsByType = [
            'apartamento' => [],
            'recibo' => [],
            'pago' => [],
            'general' => []
        ];

        foreach ($errors as $error) {
            $message = strtolower($error['message']);
            if (strpos($message, 'apartamento') !== false) {
                $errorsByType['apartamento'][] = $error;
            } elseif (strpos($message, 'recibo') !== false) {
                $errorsByType['recibo'][] = $error;
            } elseif (strpos($message, 'pago') !== false) {
                $errorsByType['pago'][] = $error;
            } else {
                $errorsByType['general'][] = $error;
            }
        }

        // Generar secciones del reporte
        foreach ($errorsByType as $type => $typeErrors) {
            if (!empty($typeErrors)) {
                $report .= "ERRORES DE " . strtoupper($type) . " (" . count($typeErrors) . ")\n";
                $report .= str_repeat('-', 50) . "\n";
                
                foreach ($typeErrors as $error) {
                    $report .= "[{$error['timestamp']}] {$error['message']}\n";
                }
                
                $report .= "\n";
            }
        }

        return $report;
    }

    /**
     * Mostrar resumen en consola
     */
    private function displaySummary($errors)
    {
        $this->info('\n--- RESUMEN DE ERRORES ---');
        
        $apartamentoErrors = array_filter($errors, function($error) {
            return strpos(strtolower($error['message']), 'apartamento') !== false;
        });
        
        $reciboErrors = array_filter($errors, function($error) {
            return strpos(strtolower($error['message']), 'recibo') !== false;
        });
        
        $pagoErrors = array_filter($errors, function($error) {
            return strpos(strtolower($error['message']), 'pago') !== false;
        });
        
        $this->line('Errores de Apartamentos: ' . count($apartamentoErrors));
        $this->line('Errores de Recibos: ' . count($reciboErrors));
        $this->line('Errores de Pagos: ' . count($pagoErrors));
        
        if (!empty($errors)) {
            $this->info('\n--- ÚLTIMOS 3 ERRORES ---');
            $lastErrors = array_slice($errors, -3);
            foreach ($lastErrors as $error) {
                $this->line("[{$error['timestamp']}] " . substr($error['message'], 0, 100) . '...');
            }
        }
    }
}