<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'membro@example.com',
            'password' => Hash::make('senha-correta'),
            'ativo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'senha' => 'senha-correta',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha-correta'),
            'ativo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'senha' => 'senha-errada',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha-correta'),
            'ativo' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'senha' => 'senha-correta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
