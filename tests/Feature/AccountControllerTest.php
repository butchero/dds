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

    public function test_imported_number_claim_is_not_available(): void
    {
        $this->get(route('login'))->assertDontSee('Am deja număr la DDS');
        $this->get(route('register'))->assertDontSee('Am deja număr la DDS');
        $this->get('/cont/revendicare')->assertNotFound();
        $this->post('/cont/revendicare', ['phone' => '0722000000'])->assertNotFound();
        $this->get('/cont/revendicare/finalizeaza')->assertNotFound();
        $this->post('/cont/revendicare/finalizeaza')->assertNotFound();
    }
}
