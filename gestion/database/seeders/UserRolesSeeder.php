<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@gestionactas.com',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'status' => true,
        ]);

        // Crear usuario propietario
        User::create([
            'name' => 'Juan Propietario',
            'email' => 'propietario@example.com',
            'password' => Hash::make('propietario123'),
            'role' => User::ROLE_USUARIO_PROPIETARIO,
            'status' => true,
        ]);

        // Crear usuario junta de vecinos
        User::create([
            'name' => 'María Junta',
            'email' => 'junta@example.com',
            'password' => Hash::make('junta123'),
            'role' => User::ROLE_USUARIO_JUNTA_VECINOS,
            'status' => true,
        ]);

        // Crear usuario admin@sc.com
        User::create([
            'name' => 'Admin SC',
            'email' => 'admin@sc.com',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'status' => true,
        ]);

        // Crear usuario edrey@sc.com
        User::create([
            'name' => 'Edrey SC',
            'email' => 'edrey@sc.com',
            'password' => Hash::make('edrey123'),
            'role' => User::ROLE_ADMIN,
            'status' => true,
        ]);

        User::updateOrCreate(
            ['email' => 'presidente@residenciasalfa.com'],
            [
                'name' => 'Presidente Junta de Condominio',
                'password' => Hash::make('alfa2026'),
                'role' => User::ROLE_USUARIO_CONDOMINIO,
                'status' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'tesorero@residenciasalfa.com'],
            [
                'name' => 'Tesorero Junta de Condominio',
                'password' => Hash::make('alfa2026'),
                'role' => User::ROLE_USUARIO_CONDOMINIO,
                'status' => true,
            ]
        );
    }
}
