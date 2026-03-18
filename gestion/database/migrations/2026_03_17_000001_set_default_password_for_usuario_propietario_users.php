<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $excludedEmails = [
            'leidyrico12@gmail.com',
            'edreyeligon@gmail.com',
        ];

        DB::table('users')
            ->where('role', 'usuario_propietario')
            ->where(function ($query) use ($excludedEmails) {
                $query->whereNull('email')
                    ->orWhereNotIn(DB::raw('LOWER(email)'), $excludedEmails);
            })
            ->update(['password' => 'alfa2026']);
    }

    public function down(): void {}
};
