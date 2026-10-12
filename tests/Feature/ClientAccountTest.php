<?php

namespace Tests\Feature;

use App\Filament\Imports\ClientImporter;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Appointment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\AppointmentRequestedNotification;
use App\Notifications\PhoneCodeNotification;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ClientAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_creates_an_unclaimed_client_and_does_not_rename_a_claimed_one(): void
    {
        ClientImporter::test([
            'name' => 'name',
            'phone' => 'phone',
        ])->import([
            'name' => 'Ion Popescu',
            'phone' => '0722 123 456',
        ])->assertImported();

        $imported = User::query()->where('phone', '0722123456')->first();
        $this->assertNotNull($imported);
        $this->assertNull($imported->email);
        $this->assertTrue($imported->isUnclaimed());

        ClientImporter::test([
            'name' => 'name',
            'phone' => 'phone',
        ])->import([
            'name' => 'Ion Actualizat',
            'phone' => '0722123456',
        ])->assertImported();

        $this->assertSame('Ion Actualizat', $imported->refresh()->name);

        $imported->forceFill([
            'email' => 'ion@example.com',
            'password' => 'parola-secreta',
        ])->save();

        ClientImporter::test([
            'name' => 'name',
            'phone' => 'phone',
        ])->import([
            'name' => 'Alt Nume',
            'phone' => '0722123456',
        ])->assertImported();

        $this->assertSame('Ion Actualizat', $imported->refresh()->name);
        $this->assertSame(1, User::query()->where('phone', '0722123456')->count());
    }

    public function test_registration_links_an_imported_phone_after_the_sms_code(): void
    {
        Notification::fake();

        User::query()->create([
            'name' => 'Din CSV',
            'phone' => '0722000000',
            'role' => 'client',
        ]);

        $this->post(route('register'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'phone' => '0722000000',
            'password' => 'parola-secreta',
            'password_confirmation' => 'parola-secreta',
        ])->assertRedirect(route('phone.confirm'));

        $this->assertSame(1, User::query()->count());

        $code = $this->sentCode();

        $this->post(route('phone.confirm'), ['code' => $code])
            ->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'ana@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('0722000000', $user->phone);
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_client_cannot_open_the_account(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('account.home'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_a_client_can_store_two_addresses_two_companies_and_one_equipment(): void
    {
        $user = User::factory()->create(['phone' => '0733000000']);

        $this->actingAs($user)->post(route('account.addresses.store'), $this->address('Acasă'))->assertRedirect();
        $this->actingAs($user)->post(route('account.addresses.store'), $this->address('Serviciu', 'Mamaia', '10'))->assertRedirect();

        $this->assertCount(2, $user->addresses);

        $home = $user->addresses()->first();

        $this->actingAs($user)->post(route('account.equipment.store'), [
            'name' => 'Centrală',
            'type' => 'centrală',
            'address_id' => $home->id,
            'interval_months' => 12,
            'last_revision_on' => '2024-01-15',
            'notify_email' => '1',
        ])->assertRedirect();

        $equipment = $user->equipment()->first();
        $this->assertSame($home->id, $equipment->address_id);
        $this->assertTrue($equipment->notify_email);
        $this->assertSame('2025-01-15', $equipment->next_revision_on->toDateString());

        $this->actingAs($user)->post(route('account.billing.store'), $this->company('Firma Unu', 'RO123'))->assertRedirect();
        $this->actingAs($user)->post(route('account.billing.store'), $this->company('Firma Doi', 'RO456'))->assertRedirect();

        $this->assertCount(2, $user->billingProfiles()->where('type', 'company')->get());

        $this->actingAs($user)->delete(route('account.addresses.delete', $home))
            ->assertSessionHasErrors('address');
        $this->assertModelExists($home);
    }

    public function test_appointment_uses_the_only_company_and_emails_the_firm(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $address = $user->addresses()->create($this->address('Acasă'));
        $company = $user->billingProfiles()->create($this->company('Firma Unu', 'RO123'));
        $equipment = $user->equipment()->create([
            'name' => 'Centrală',
            'address_id' => $address->id,
            'interval_months' => 24,
        ]);

        $this->actingAs($user)->post(route('account.appointment'), [
            'address_id' => $address->id,
            'billing_type' => 'company',
            'equipment_id' => $equipment->id,
            'requested_on' => '2026-11-01',
            'note' => 'Revizie',
        ])->assertRedirect(route('account.appointments'));

        $appointment = Appointment::query()->first();
        $this->assertSame('requested', $appointment->status);
        $this->assertSame($company->id, $appointment->billing_profile_id);
        $this->assertSame('vali.ciuca@gmail.com', SiteSetting::get('company_email'));
        Notification::assertSentOnDemand(AppointmentRequestedNotification::class);
    }

    public function test_appointment_asks_for_equipment_before_it_can_be_sent(): void
    {
        $user = User::factory()->create();
        $address = $user->addresses()->create($this->address('Acasă'));

        $this->actingAs($user)->get(route('account.appointments'))
            ->assertOk()
            ->assertSee('Nu ai niciun echipament. Adaugă-l înainte să trimiți cererea.');

        $this->actingAs($user)->post(route('account.appointment'), [
            'address_id' => $address->id,
            'billing_type' => 'individual',
        ])->assertSessionHasErrors('equipment_id');

        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_appointment_keeps_the_chosen_company_and_history_shows_a_finished_visit(): void
    {
        $user = User::factory()->create();
        $address = $user->addresses()->create($this->address('Acasă'));
        $first = $user->billingProfiles()->create($this->company('Firma Unu', 'RO123'));
        $second = $user->billingProfiles()->create($this->company('Firma Doi', 'RO456'));
        $equipment = $user->equipment()->create([
            'name' => 'Centrală',
            'address_id' => $address->id,
            'interval_months' => 24,
            'last_revision_on' => '2020-01-01',
        ]);

        $this->actingAs($user)->post(route('account.appointment'), [
            'address_id' => $address->id,
            'billing_type' => 'company',
            'company_profile_id' => $second->id,
            'equipment_id' => $equipment->id,
            'requested_on' => '2026-05-02',
        ])->assertRedirect();

        $appointment = Appointment::query()->first();
        $this->assertSame($second->id, $appointment->billing_profile_id);
        $this->assertNotSame($first->id, $appointment->billing_profile_id);

        $appointment->update(['status' => 'done']);

        $this->assertSame('2026-05-02', $equipment->refresh()->last_revision_on->toDateString());
        $this->assertSame('2028-05-02', $equipment->next_revision_on->toDateString());

        $this->actingAs($user)->get(route('account.appointments'))
            ->assertSee('Efectuată')
            ->assertSee('Firma Doi')
            ->assertDontSee('Salvează datele');
    }

    public function test_adding_a_phone_absorbs_the_imported_client(): void
    {
        Notification::fake();

        $imported = User::query()->create([
            'name' => 'Din CSV',
            'phone' => '0744000000',
            'role' => 'client',
        ]);
        $address = $imported->addresses()->create($this->address('Acasă'));

        $user = User::factory()->create(['phone' => null]);

        $this->actingAs($user)->post(route('account.phone'), [
            'phone' => '0744000000',
        ])->assertRedirect(route('phone.confirm'));

        $this->actingAs($user)->post(route('phone.confirm'), [
            'code' => $this->sentCode(),
        ])->assertRedirect(route('account.home'));

        $this->assertSame('0744000000', $user->refresh()->phone);
        $this->assertSame($user->id, $address->refresh()->user_id);
        $this->assertModelMissing($imported);
    }

    public function test_google_login_marks_the_email_verified_and_rejects_a_missing_email(): void
    {
        $this->mockSocialite('google', 'google-1', 'maria@example.com', 'Maria');

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('account.home'));

        $user = User::query()->where('email', 'maria@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->socialAccounts()->where('provider', 'google')->exists());

        $this->mockSocialite('google', 'google-2', null, 'Fără email');

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::query()->count());
    }

    public function test_account_sections_have_their_own_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.home'))
            ->assertOk()
            ->assertSee('Salvează datele')
            ->assertDontSee('Adaugă adresa');

        $this->actingAs($user)->get(route('account.addresses'))
            ->assertOk()
            ->assertSee('Adaugă adresa')
            ->assertSee('CONTUL MEU')
            ->assertDontSee('>Categorii<', false)
            ->assertSee('aria-current="page"', false);

        $this->actingAs($user)->get(route('account.equipment'))->assertOk()->assertSee('Echipamente');
        $this->actingAs($user)->get(route('account.billing'))->assertOk()->assertSee('Adaugă facturarea');
        $this->actingAs($user)->get(route('account.appointments'))->assertOk()->assertSee('Programează o intervenție');
    }

    public function test_admin_can_save_an_address_from_the_client_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditUser::class, ['record' => $client->getRouteKey()])
            ->fillForm([
                'addresses' => [
                    [
                        'county' => 'Constanța',
                        'city' => 'Constanța',
                        'street' => 'Mamaia',
                        'street_number' => '70',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('addresses', [
            'user_id' => $client->id,
            'street' => 'Mamaia',
        ]);
    }

    public function test_a_used_phone_is_rejected(): void
    {
        User::factory()->create(['phone' => '0755000000']);

        $this->post(route('register'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'phone' => '0755000000',
            'password' => 'parola-secreta',
            'password_confirmation' => 'parola-secreta',
        ])->assertSessionHasErrors('phone');
    }

    /**
     * @param  array<string, string|null>  $overrides
     * @return array<string, string|null>
     */
    private function address(string $label, string $street = 'Stefan cel Mare', string $number = '1'): array
    {
        return [
            'label' => $label,
            'county' => 'Constanța',
            'city' => 'Constanța',
            'street' => $street,
            'street_number' => $number,
            'postal_code' => '900001',
            'details' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function company(string $name, string $cui): array
    {
        return [
            'type' => 'company',
            'company_name' => $name,
            'cui' => $cui,
            'registration_number' => 'J13/1/2020',
            'is_vat_payer' => true,
            'county' => 'Constanța',
            'city' => 'Constanța',
            'street' => 'Mamaia',
            'street_number' => '70',
        ];
    }

    private function sentCode(): string
    {
        $code = null;

        Notification::assertSentOnDemand(
            PhoneCodeNotification::class,
            function (PhoneCodeNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            },
        );

        return (string) $code;
    }

    private function mockSocialite(string $provider, string $id, ?string $email, string $name): void
    {
        $socialiteUser = (new SocialiteUser)->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $driver = Mockery::mock();
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('scopes')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with($provider)->andReturn($driver);
    }
}
