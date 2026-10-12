<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteConfiguration;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_is_redirected_from_site_settings(): void
    {
        $this->get('/dds-panel/site-configuration')
            ->assertRedirect('/dds-panel/login');
    }

    public function test_client_cannot_open_site_settings(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get('/dds-panel/site-configuration')
            ->assertForbidden();
    }

    public function test_admin_can_open_site_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dds-panel/site-configuration')
            ->assertOk()
            ->assertSee('Titlul primei pagini');
    }

    public function test_admin_saves_the_home_heading(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'home_heading' => 'Titlu nou',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Setările au fost salvate.');

        $this->assertDatabaseHas('site_settings', [
            'name' => 'home_heading',
            'value' => 'Titlu nou',
        ]);
    }

    public function test_blank_home_heading_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('home_heading', 'Titlu vechi');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'home_heading' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'home_heading' => 'required',
            ])
            ->assertSee('Câmpul titlul primei pagini este obligatoriu.')
            ->assertNotNotified();

        $this->assertDatabaseHas('site_settings', [
            'name' => 'home_heading',
            'value' => 'Titlu vechi',
        ]);
    }

    public function test_admin_saves_the_meta_description(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'meta_description' => 'Descriere pentru motoarele de căutare.',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Setările au fost salvate.');

        $this->assertDatabaseHas('site_settings', [
            'name' => 'meta_description',
            'value' => 'Descriere pentru motoarele de căutare.',
        ]);
    }

    public function test_blank_meta_description_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('meta_description', 'Descriere veche');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'meta_description' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'meta_description' => 'required',
            ])
            ->assertSee('Câmpul descrierea implicită este obligatoriu.')
            ->assertNotNotified();

        $this->assertDatabaseHas('site_settings', [
            'name' => 'meta_description',
            'value' => 'Descriere veche',
        ]);
    }

    public function test_admin_saves_the_contact_office_and_coordinates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'contact_company' => 'Firma Nouă',
                'contact_address' => 'Strada Nouă 1',
                'contact_city' => 'Mamaia',
                'contact_email' => 'nou@example.com',
                'contact_phone' => '0241-000000',
                'contact_fax' => '',
                'contact_mobile' => '0733-111222',
                'contact_mobile_alt' => '',
                'contact_latitude' => '44.2',
                'contact_longitude' => '28.6',
                'company_email' => 'mesaje@example.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Setările au fost salvate.');

        $this->assertSame('Firma Nouă', SiteSetting::get('contact_company'));
        $this->assertSame('Strada Nouă 1', SiteSetting::get('contact_address'));
        $this->assertSame('Mamaia', SiteSetting::get('contact_city'));
        $this->assertSame('nou@example.com', SiteSetting::get('contact_email'));
        $this->assertSame('0241-000000', SiteSetting::get('contact_phone'));
        $this->assertSame('', SiteSetting::get('contact_fax'));
        $this->assertSame('0733-111222', SiteSetting::get('contact_mobile'));
        $this->assertSame('', SiteSetting::get('contact_mobile_alt'));
        $this->assertSame('44.2', SiteSetting::get('contact_latitude'));
        $this->assertSame('28.6', SiteSetting::get('contact_longitude'));
        $this->assertSame('mesaje@example.com', SiteSetting::get('company_email'));
    }

    public function test_blank_contact_company_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('contact_company', 'Firmă veche');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'contact_company' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'contact_company' => 'required',
            ])
            ->assertNotNotified();

        $this->assertSame('Firmă veche', SiteSetting::get('contact_company'));
    }

    public function test_admin_saves_the_copyright_start_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'copyright_start_year' => '1995',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Setările au fost salvate.');

        $this->assertSame('1995', SiteSetting::get('copyright_start_year'));
    }

    public function test_blank_copyright_start_year_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('copyright_start_year', '2008');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'copyright_start_year' => '',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'copyright_start_year' => 'required',
            ])
            ->assertSee('Câmpul anul de început este obligatoriu.')
            ->assertNotNotified();

        $this->assertSame('2008', SiteSetting::get('copyright_start_year'));
    }

    public function test_copyright_start_year_must_be_an_integer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('copyright_start_year', '2008');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'copyright_start_year' => 'doi',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'copyright_start_year' => 'numeric',
            ])
            ->assertSee('Câmpul anul de început trebuie să fie un număr.')
            ->assertNotNotified();

        $this->assertSame('2008', SiteSetting::get('copyright_start_year'));
    }

    public function test_copyright_start_year_after_this_year_is_rejected(): void
    {
        $this->travelTo('2026-10-09');
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('copyright_start_year', '2008');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'copyright_start_year' => '2027',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'copyright_start_year' => 'max',
            ])
            ->assertSee('Câmpul anul de început nu poate fi mai mare de 2026.')
            ->assertNotNotified();

        $this->assertSame('2008', SiteSetting::get('copyright_start_year'));
    }

    public function test_invalid_contact_latitude_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SiteSetting::set('contact_latitude', '44.1878643');

        Livewire::actingAs($admin)
            ->test(SiteConfiguration::class)
            ->fillForm([
                'contact_latitude' => 'nord',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'contact_latitude' => 'numeric',
            ])
            ->assertNotNotified();

        $this->assertSame('44.1878643', SiteSetting::get('contact_latitude'));
    }
}
