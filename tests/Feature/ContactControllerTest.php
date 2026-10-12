<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Notifications\ContactMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_shows_the_office_the_form_and_the_map(): void
    {
        $response = $this->get(route('contact'));

        $response->assertSee('<h1 class="text-lg font-bold uppercase tracking-wide">Contact</h1>', false);
        $response->assertSee('Formular de contact');
        $response->assertSee('Localizare');
        $response->assertSee('Informații de contact');
        $response->assertSee('Trimite mesaj');
        $response->assertDontSee('>STIRI<', false);
        $response->assertSee('SC DDS SERVICES GROUP SRL');
        $response->assertSee('Bd. Mamaia nr. 70, bl. Bi 1, parter');
        $response->assertSee('Constanța');
        $response->assertSee('0241-619619');
        $response->assertSee('0241-619699');
        $response->assertSee('0722-462289');
        $response->assertSee('0722-462279');
        $response->assertSee('office@dds-constanta.ro');
        $response->assertSee('https://maps.google.com/maps?hl=ro&amp;z=17&amp;output=embed&amp;q=44.1878643,28.6507139', false);
        $response->assertSee('Vizualizare hartă mărită');
        $response->assertSee('Vânzări');
        $response->assertSee('Suport tehnic');
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee(route('contact.challenge'), false);
    }

    public function test_contact_page_uses_the_saved_office_and_coordinates(): void
    {
        SiteSetting::set('contact_company', 'Firma Nouă');
        SiteSetting::set('contact_address', 'Strada Nouă 1');
        SiteSetting::set('contact_city', 'Mamaia');
        SiteSetting::set('contact_email', 'nou@example.com');
        SiteSetting::set('contact_phone', '0241-000000');
        SiteSetting::set('contact_fax', '');
        SiteSetting::set('contact_mobile', '0733-111222');
        SiteSetting::set('contact_mobile_alt', '');
        SiteSetting::set('contact_latitude', '44.2');
        SiteSetting::set('contact_longitude', '28.6');

        $response = $this->get(route('contact'));

        $response->assertSee('Firma Nouă');
        $response->assertSee('Strada Nouă 1');
        $response->assertSee('Mamaia');
        $response->assertSee('nou@example.com');
        $response->assertSee('0241-000000');
        $response->assertSee('tel:+40241000000', false);
        $response->assertSee('0733-111222');
        $response->assertSee('tel:+40733111222', false);
        $response->assertDontSee('0241-619699');
        $response->assertDontSee('0722-462279');
        $response->assertSee('https://maps.google.com/maps?hl=ro&amp;z=17&amp;output=embed&amp;q=44.2,28.6', false);
    }

    public function test_contact_page_hides_the_map_when_the_coordinates_are_invalid(): void
    {
        SiteSetting::set('contact_latitude', 'nord');
        SiteSetting::set('contact_longitude', '28.6');

        $this->get(route('contact'))
            ->assertDontSee('maps.google.com', false)
            ->assertDontSee('Vizualizare hartă mărită');
    }

    public function test_verification_image_is_missing_until_the_page_sets_a_code(): void
    {
        $this->get(route('contact.challenge'))->assertNotFound();
    }

    public function test_old_contact_address_redirects_to_the_contact_page(): void
    {
        $this->get('/servicii/contact')->assertRedirect(route('contact'));
    }

    public function test_empty_contact_form_asks_for_every_field(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), [])
            ->assertSessionHasErrors([
                'name' => 'Câmpul numele este obligatoriu.',
                'email' => 'Câmpul e-mailul este obligatoriu.',
                'phone' => 'Câmpul telefonul este obligatoriu.',
                'department' => 'Câmpul departamentul este obligatoriu.',
                'message' => 'Câmpul mesajul este obligatoriu.',
                'verification_code' => 'Câmpul codul de verificare este obligatoriu.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_invalid_email_is_rejected(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), $this->message([
            'email' => 'nu-este-email',
        ]))->assertSessionHasErrors([
            'email' => 'Câmpul e-mailul trebuie să fie o adresă de e-mail validă.',
        ]);

        Notification::assertNothingSent();
    }

    public function test_unknown_department_is_rejected(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), $this->message([
            'department' => 'contabilitate',
        ]))->assertSessionHasErrors([
            'department' => 'Câmpul departamentul selectat nu este valid.',
        ]);

        Notification::assertNothingSent();
    }

    public function test_name_with_a_new_line_is_rejected(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), $this->message([
            'name' => "Ana\r\nBcc: bad@example.com",
        ]))->assertSessionHasErrors([
            'name' => 'Numele nu poate conține rânduri noi.',
        ]);

        Notification::assertNothingSent();
    }

    public function test_wrong_verification_code_is_rejected_and_the_name_is_escaped(): void
    {
        Notification::fake();

        $this->from(route('contact'))->post(route('contact.store'), $this->message([
            'name' => '<script>alert(1)</script>',
            'verification_code' => 'WRONG',
        ]))->assertRedirect(route('contact'));

        $response = $this->get(route('contact'));

        $response->assertSee('Codul de verificare nu este corect.');
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        Notification::assertNothingSent();
    }

    public function test_a_filled_honeypot_does_not_send_mail(): void
    {
        Notification::fake();

        $this->followingRedirects()->post(route('contact.store'), [
            'company_url' => 'https://spam.example',
            'name' => 'Bot',
        ])->assertSee('Mesajul a fost trimis. Vă contactăm în curând.');

        Notification::assertNothingSent();
    }

    public function test_message_is_mailed_to_the_company_and_html_is_escaped(): void
    {
        Notification::fake();

        $this->post(route('contact.store'), $this->message([
            'message' => 'Vreau o ofertă. <script>alert(1)</script>',
        ]))->assertRedirect(route('contact'));

        $this->get(route('contact'))->assertSee('Mesajul a fost trimis. Vă contactăm în curând.');

        Notification::assertSentOnDemand(
            ContactMessageNotification::class,
            function (ContactMessageNotification $notification, array $channels, object $notifiable): bool {
                $mail = $notification->toMail($notifiable);
                $html = $mail->render();

                $this->assertSame(['mail'], $channels);
                $this->assertSame('vali.ciuca@gmail.com', $notifiable->routes['mail']);
                $this->assertSame('Mesaj de contact - Vânzări', $mail->subject);
                $this->assertSame(['ana@example.com', 'Ana Pop'], $mail->replyTo[0]);
                $this->assertStringContainsString('Vreau o ofertă.', $html);
                $this->assertStringContainsString('&lt;script&gt;', $html);
                $this->assertStringNotContainsString('<script>', $html);

                return true;
            },
        );
    }

    public function test_message_is_kept_when_the_company_email_is_missing(): void
    {
        Notification::fake();
        SiteSetting::set('company_email', '');

        $this->from(route('contact'))->post(route('contact.store'), $this->message())
            ->assertSessionHasErrors([
                'message' => 'Mesajul nu a putut fi trimis. Te rugăm să ne suni sau să ne scrii pe e-mail.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_sixth_contact_post_in_a_minute_is_rejected(): void
    {
        $response = null;

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('contact.store'), []);
        }

        $response->assertStatus(429);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function message(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Pop',
            'email' => 'ana@example.com',
            'phone' => '0722111222',
            'department' => 'vanzari',
            'message' => 'Vreau o ofertă.',
            'verification_code' => $this->verificationCode(),
        ], $overrides);
    }

    private function verificationCode(): string
    {
        $this->get(route('contact'));

        $svg = $this->get(route('contact.challenge'))->assertOk()->getContent();
        preg_match_all('/<text\b[^>]*>([^<]*)<\/text>/', $svg, $matches);

        return implode('', $matches[1]);
    }
}
