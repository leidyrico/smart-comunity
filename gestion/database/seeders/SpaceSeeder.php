<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Space;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = [
            [
                'nombre' => 'Salón de Eventos',
                'descripcion' => 'Amplio salón para celebraciones y eventos sociales con capacidad para 100 personas.',
                'precio_por_dia' => 150.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Piscina',
                'descripcion' => 'Área de piscina con zona de descanso y parrillas para uso recreativo.',
                'precio_por_dia' => 80.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Cancha de Tenis',
                'descripcion' => 'Cancha de tenis profesional con iluminación nocturna.',
                'precio_por_dia' => 60.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Sala de Reuniones',
                'descripcion' => 'Sala equipada para reuniones corporativas con proyector y aire acondicionado.',
                'precio_por_dia' => 40.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Gimnasio',
                'descripcion' => 'Gimnasio completamente equipado con máquinas de ejercicio modernas.',
                'precio_por_dia' => 25.00,
                'activo' => false, // Inactive for testing
            ],
        ];

        foreach ($spaces as $space) {
            Space::create($space);
        }
    }
}
