<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_login_shows_the_error_on_the_form(): void
    {
        User::factory()->create([
            'email' => 'client@example.com',
        ]);

        $this->followingRedirects()
            ->from(route('login'))
            ->post(route('login'), [
                'email' => 'client@example.com',
                'password' => 'parola-gresita',
            ])
            ->assertSee('Datele de autentificare nu sunt corecte.');
    }
}
