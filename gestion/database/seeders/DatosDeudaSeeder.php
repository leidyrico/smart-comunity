<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Carbon\Carbon;

class DatosDeudaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes
        Pago::query()->delete();
        ReciboGastoComun::query()->delete();
        Apartamento::query()->delete();
        
        // Crear apartamentos de prueba
        $apartamentos = [
            ['numero' => '101', 'propietario' => 'Juan Pérez García', 'telefono' => '3001234567', 'email' => 'juan.perez@email.com'],
            ['numero' => '102', 'propietario' => 'María González López', 'telefono' => '3007654321', 'email' => 'maria.gonzalez@email.com'],
            ['numero' => '201', 'propietario' => 'Carlos Rodríguez Silva', 'telefono' => '3009876543', 'email' => 'carlos.rodriguez@email.com'],
            ['numero' => '202', 'propietario' => 'Ana Martínez Ruiz', 'telefono' => '3005432109', 'email' => 'ana.martinez@email.com'],
            ['numero' => '301', 'propietario' => 'Luis Fernando Castro', 'telefono' => '3002468135', 'email' => 'luis.castro@email.com'],
            ['numero' => '302', 'propietario' => 'Sandra Patricia Morales', 'telefono' => '3008642097', 'email' => 'sandra.morales@email.com'],
            ['numero' => '401', 'propietario' => 'Roberto Jiménez Vargas', 'telefono' => '3001357924', 'email' => 'roberto.jimenez@email.com'],
            ['numero' => '402', 'propietario' => 'Carmen Elena Herrera', 'telefono' => '3009753186', 'email' => 'carmen.herrera@email.com']
        ];

        $apartamentosCreados = [];
        foreach ($apartamentos as $apartamento) {
            $apartamentosCreados[] = Apartamento::create($apartamento);
        }

        // Crear recibos de gasto común
        $recibos = [
            [
                'numero_recibo' => 'RGC-2024-001',
                'periodo' => 'Enero 2024',
                'fecha_emision' => Carbon::create(2024, 1, 15),
                'fecha_vencimiento' => Carbon::create(2024, 2, 15),
                'valor_administracion' => 80000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 15000,
                'otros_conceptos' => 0,
                'total_recibo' => 150000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes enero 2024'
            ],
            [
                'numero_recibo' => 'RGC-2024-002',
                'periodo' => 'Febrero 2024',
                'fecha_emision' => Carbon::create(2024, 2, 15),
                'fecha_vencimiento' => Carbon::create(2024, 3, 15),
                'valor_administracion' => 82000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 18000,
                'otros_conceptos' => 0,
                'total_recibo' => 155000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes febrero 2024'
            ],
            [
                'numero_recibo' => 'RGC-2024-003',
                'periodo' => 'Marzo 2024',
                'fecha_emision' => Carbon::create(2024, 3, 15),
                'fecha_vencimiento' => Carbon::create(2024, 4, 15),
                'valor_administracion' => 85000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 20000,
                'otros_conceptos' => 0,
                'total_recibo' => 160000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes marzo 2024'
            ],
            [
                'numero_recibo' => 'RGC-2024-004',
                'periodo' => 'Abril 2024',
                'fecha_emision' => Carbon::create(2024, 4, 15),
                'fecha_vencimiento' => Carbon::create(2024, 5, 15),
                'valor_administracion' => 83000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 20000,
                'otros_conceptos' => 0,
                'total_recibo' => 158000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes abril 2024'
            ],
            [
                'numero_recibo' => 'RGC-2024-005',
                'periodo' => 'Mayo 2024',
                'fecha_emision' => Carbon::create(2024, 5, 15),
                'fecha_vencimiento' => Carbon::create(2024, 6, 15),
                'valor_administracion' => 87000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 20000,
                'otros_conceptos' => 0,
                'total_recibo' => 162000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes mayo 2024'
            ],
            [
                'numero_recibo' => 'RGC-2024-006',
                'periodo' => 'Junio 2024',
                'fecha_emision' => Carbon::create(2024, 6, 15),
                'fecha_vencimiento' => Carbon::create(2024, 7, 15),
                'valor_administracion' => 90000,
                'valor_aseo' => 25000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 20000,
                'otros_conceptos' => 0,
                'total_recibo' => 165000,
                'estado' => 'activo',
                'observaciones' => 'Gastos comunes junio 2024'
            ]
        ];

        $recibosCreados = [];
        foreach ($recibos as $recibo) {
            $recibosCreados[] = ReciboGastoComun::create($recibo);
        }

        // Crear algunos pagos de prueba usando los IDs reales
        $pagosCompletos = [
            // Apartamento 101 - Juan Pérez - Pagos completos enero y febrero
            ['apartamento_id' => $apartamentosCreados[0]->id, 'recibo_id' => $recibosCreados[0]->id, 'monto' => 150000, 'fecha' => '2024-01-20'],
            ['apartamento_id' => $apartamentosCreados[0]->id, 'recibo_id' => $recibosCreados[1]->id, 'monto' => 155000, 'fecha' => '2024-02-18'],
            
            // Apartamento 102 - María González - Pagos completos enero, febrero y marzo
            ['apartamento_id' => $apartamentosCreados[1]->id, 'recibo_id' => $recibosCreados[0]->id, 'monto' => 150000, 'fecha' => '2024-01-25'],
            ['apartamento_id' => $apartamentosCreados[1]->id, 'recibo_id' => $recibosCreados[1]->id, 'monto' => 155000, 'fecha' => '2024-02-22'],
            ['apartamento_id' => $apartamentosCreados[1]->id, 'recibo_id' => $recibosCreados[2]->id, 'monto' => 160000, 'fecha' => '2024-03-20'],
            
            // Apartamento 201 - Carlos Rodríguez - Pago parcial enero
            ['apartamento_id' => $apartamentosCreados[2]->id, 'recibo_id' => $recibosCreados[0]->id, 'monto' => 100000, 'fecha' => '2024-01-30'],
            
            // Apartamento 301 - Luis Castro - Pagos completos enero, febrero, marzo y abril
            ['apartamento_id' => $apartamentosCreados[4]->id, 'recibo_id' => $recibosCreados[0]->id, 'monto' => 150000, 'fecha' => '2024-01-15'],
            ['apartamento_id' => $apartamentosCreados[4]->id, 'recibo_id' => $recibosCreados[1]->id, 'monto' => 155000, 'fecha' => '2024-02-15'],
            ['apartamento_id' => $apartamentosCreados[4]->id, 'recibo_id' => $recibosCreados[2]->id, 'monto' => 160000, 'fecha' => '2024-03-15'],
            ['apartamento_id' => $apartamentosCreados[4]->id, 'recibo_id' => $recibosCreados[3]->id, 'monto' => 158000, 'fecha' => '2024-04-15'],
            
            // Apartamento 302 - Sandra Morales - Pago parcial febrero
            ['apartamento_id' => $apartamentosCreados[5]->id, 'recibo_id' => $recibosCreados[1]->id, 'monto' => 80000, 'fecha' => '2024-02-28'],
        ];

        foreach ($pagosCompletos as $pago) {
            Pago::create([
                'apartamento_id' => $pago['apartamento_id'],
                'recibo_gasto_comun_id' => $pago['recibo_id'],
                'monto_pagado' => $pago['monto'],
                'fecha_pago' => Carbon::parse($pago['fecha']),
                'metodo_pago' => 'transferencia',
                'numero_comprobante' => 'COMP-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'estado' => 'confirmado',
                'observaciones' => 'Pago de prueba generado por seeder'
            ]);
        }
    }
}