<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class PlainTextUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];
        $storedPassword = $user->getAuthPassword();
        
        // Primero intentar comparación directa (texto plano)
        if ($plain === $storedPassword) {
            return true;
        }
        
        // Si no coincide, intentar verificación de hash
        return $this->hasher->check($plain, $storedPassword);
    }
}