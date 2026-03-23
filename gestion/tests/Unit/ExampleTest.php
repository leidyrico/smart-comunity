<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    public function test_usuario_condominio_tiene_privilegio_admin(): void
    {
        $user = new User;
        $user->role = User::ROLE_USUARIO_CONDOMINIO;

        $this->assertTrue($user->isAdmin());
    }
}
