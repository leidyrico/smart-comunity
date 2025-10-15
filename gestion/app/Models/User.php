<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Constantes para los roles
    const ROLE_USUARIO_PROPIETARIO = 'usuario_propietario';
    const ROLE_USUARIO_JUNTA_VECINOS = 'usuario_junta_vecinos';
    const ROLE_ADMIN = 'admin';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'apartamento_id',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'password' => 'hashed', // Deshabilitado para permitir texto plano
        ];
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Verificar si el usuario es propietario
     */
    public function isUsuarioPropietario()
    {
        return $this->role === self::ROLE_USUARIO_PROPIETARIO;
    }

    /**
     * Verificar si el usuario es de junta de vecinos
     */
    public function isUsuarioJuntaVecinos()
    {
        return $this->role === self::ROLE_USUARIO_JUNTA_VECINOS;
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Obtener todos los roles disponibles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_USUARIO_PROPIETARIO => 'Usuario Propietario',
            self::ROLE_USUARIO_JUNTA_VECINOS => 'Usuario Junta de Vecinos',
            self::ROLE_ADMIN => 'Administrador',
        ];
    }

    /**
     * Relación con Apartamento
     */
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }
}
