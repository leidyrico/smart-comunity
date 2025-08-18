<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
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
    }
}
