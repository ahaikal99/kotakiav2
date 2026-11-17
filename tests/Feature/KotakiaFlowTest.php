<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class KotakiaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        config(['kotakia.payment.gateway' => 'simulasi']);
        Storage::fake('public');
    }

    protected function draft(User $user, string $package = 'premium'): Invitation
    {
        $this->actingAs($user)->post(route('invitations.store'), [
            'package_id' => Package::where('slug', $package)->value('id'),
            'theme_id' => Theme::where('slug', 'emas-diraja')->value('id'),
            'groom_nickname' => 'Adam',
            'bride_nickname' => 'Hawa',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '11:00',
            'venue_name' => 'Dewan Seri Kenanga',
        ])->assertRedirect();

        return $user->invitations()->latest('id')->firstOrFail();
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Pakej & Harga', false);
        foreach (Theme::all() as $theme) {
            $this->get(route('theme.demo', $theme))->assertOk()->assertSee('Adam');
        }
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_register_creates_customer(): void
    {
        $this->post(route('register'), [
            'name' => 'Aisyah', 'email' => 'aisyah@example.com', 'phone' => '0123456789',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertSame('customer', User::firstWhere('email', 'aisyah@example.com')->role);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_full_flow_create_pay_share_rsvp(): void
    {
        $user = User::factory()->create(['phone' => '0123456789']);
        $invitation = $this->draft($user);

        $this->assertTrue($invitation->isDraft());
        $this->get(route('invitations.edit', $invitation))->assertOk();
        $this->get(route('invitations.preview', $invitation))->assertOk()->assertSee('PRATONTON');
        $this->get(route('invitations.show', $invitation))->assertOk()->assertSee('Bayar & Aktifkan', false);

        // Belum bayar: tetamu tidak boleh lihat
        auth()->logout();
        $this->get(route('invitation.show', $invitation->slug))->assertNotFound();
        $this->actingAs($user);

        // Kemas kini butiran + muat naik
        $this->put(route('invitations.update', $invitation), [
            'theme_id' => $invitation->theme_id,
            'package_id' => $invitation->package_id,
            'slug' => 'adam-hawa-2026',
            'event_title' => 'Walimatulurus',
            'groom_nickname' => 'Adam', 'bride_nickname' => 'Hawa',
            'groom_fullname' => 'Muhammad Adam bin Ismail',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '11:00', 'end_time' => '16:00',
            'venue_name' => 'Dewan Seri Kenanga',
            'maps_url' => 'https://maps.google.com/?q=KL',
            'schedule' => [['time' => '11:00 AM', 'title' => 'Ketibaan tetamu'], ['time' => '', 'title' => '']],
            'contacts' => [['name' => 'Abdullah', 'relation' => 'Bapa', 'phone' => '0123456789']],
            'rsvp_enabled' => 1,
            'max_pax_per_rsvp' => 4,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 1200),
            'music_file' => UploadedFile::fake()->create('lagu.mp3', 500, 'audio/mpeg'),
            'gift' => ['bank_name' => 'Maybank', 'account_name' => 'Hawa', 'account_no' => '123456'],
        ])->assertRedirect(route('invitations.edit', $invitation))->assertSessionHasNoErrors();

        $invitation->refresh();
        $this->assertSame('adam-hawa-2026', $invitation->slug);
        $this->assertCount(1, $invitation->schedule);
        $this->assertNotNull($invitation->cover_image);
        Storage::disk('public')->assertExists($invitation->cover_image);

        // Galeri
        $this->post(route('invitations.gallery.store', $invitation), [
            'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertSessionHasNoErrors();
        $this->assertSame(2, $invitation->gallery()->count());

        // Checkout -> simulator -> berjaya
        $redirect = $this->post(route('invitations.checkout', $invitation))->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('/bayaran/simulasi/', $redirect);
        $payment = Payment::latest('id')->first();
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(59.00, (float) $payment->amount);

        $this->get($redirect)->assertOk()->assertSee('Bayar Berjaya');

        // Return tanpa tandatangan sah tidak mengaktifkan
        $this->get(route('payment.return', ['ref' => $payment->reference, 'result' => 'success']));
        $this->assertSame('pending', $payment->fresh()->status);

        $success = URL::temporarySignedRoute('payment.return', now()->addHour(), ['ref' => $payment->reference, 'result' => 'success']);
        $this->get($success)->assertRedirect(route('invitations.show', $invitation))->assertSessionHas('paid');

        $invitation->refresh();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertTrue($invitation->isActive());
        $this->assertTrue($invitation->expires_at->greaterThan($invitation->event_date));

        // Halaman urus: pautan + ayat jemputan dijana
        $this->get(route('invitations.show', $invitation))
            ->assertOk()
            ->assertSee($invitation->url())
            ->assertSee('Assalamualaikum', false);
        $this->assertStringContainsString('?to=Pak%20Long', $invitation->shareMessage('Pak Long'));

        // Tetamu buka kad & RSVP
        auth()->logout();
        $this->get(route('invitation.show', $invitation->slug).'?to=Pak+Long')
            ->assertOk()->assertSee('Pak Long')->assertSee('Muhammad Adam bin Ismail')->assertSee('Maybank');
        $this->assertSame(1, $invitation->fresh()->views);

        $this->from(route('invitation.show', $invitation->slug))
            ->post(route('invitation.rsvp', $invitation->slug), [
                'name' => 'Pak Long', 'phone' => '0191234567', 'attendance' => 'hadir', 'pax' => 3, 'wish' => 'Tahniah!',
            ])->assertRedirect()->assertSessionHas('rsvp_success');

        $this->post(route('invitation.rsvp', $invitation->slug), ['name' => 'Mak Ngah', 'attendance' => 'tidak'])
            ->assertSessionHas('rsvp_success');

        $this->post(route('invitation.rsvp', $invitation->slug), ['name' => 'X', 'attendance' => 'hadir', 'pax' => 99])
            ->assertSessionHasErrors('pax');

        $stats = $invitation->fresh()->rsvpStats();
        $this->assertSame(['responses' => 2, 'attending' => 1, 'not_attending' => 1, 'pax' => 3, 'wishes' => 1], $stats);

        $this->get(route('invitation.show', $invitation->slug))->assertSee('Tahniah!');
        $this->get(route('invitation.calendar', $invitation->slug))->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        // Pemilik: senarai & eksport RSVP
        $this->actingAs($user);
        $this->get(route('invitations.rsvp.index', $invitation))->assertOk()->assertSee('Pak Long');
        $csv = $this->get(route('invitations.rsvp.export', $invitation))->assertOk()->streamedContent();
        $this->assertStringContainsString('Pak Long', $csv);
        $this->get(route('dashboard'))->assertOk()->assertSee('Adam & Hawa');
        $this->get(route('payments.index'))->assertOk()->assertSee($payment->reference);
    }

    public function test_failed_payment_keeps_draft(): void
    {
        $user = User::factory()->create();
        $invitation = $this->draft($user);
        $this->post(route('invitations.checkout', $invitation));
        $payment = Payment::latest('id')->first();

        $fail = URL::temporarySignedRoute('payment.return', now()->addHour(), ['ref' => $payment->reference, 'result' => 'fail']);
        $this->get($fail)->assertSessionHas('error');

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertTrue($invitation->fresh()->isDraft());
    }

    public function test_package_restrictions(): void
    {
        $user = User::factory()->create();
        $invitation = $this->draft($user, 'asas');

        // Tema premium tidak dibenarkan untuk pakej Asas
        $this->put(route('invitations.update', $invitation), [
            'theme_id' => Theme::where('slug', 'malam-songket')->value('id'),
            'package_id' => $invitation->package_id, 'slug' => $invitation->slug,
            'event_title' => 'Walimatulurus', 'groom_nickname' => 'A', 'bride_nickname' => 'B',
            'event_date' => now()->addMonth()->toDateString(), 'start_time' => '11:00',
            'venue_name' => 'Dewan', 'max_pax_per_rsvp' => 5,
        ])->assertSessionHasErrors('theme_id');

        // Galeri tidak termasuk
        $this->post(route('invitations.gallery.store', $invitation), ['images' => [UploadedFile::fake()->image('a.jpg')]])
            ->assertSessionHas('error');
        $this->assertSame(0, $invitation->gallery()->count());
    }

    public function test_users_cannot_access_others_invitations(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->draft($owner);
        $intruder = User::factory()->create();

        $this->actingAs($intruder);
        $this->get(route('invitations.show', $invitation))->assertForbidden();
        $this->get(route('invitations.edit', $invitation))->assertForbidden();
        $this->get(route('invitations.rsvp.export', $invitation))->assertForbidden();
        $this->post(route('invitations.checkout', $invitation))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_pages_and_manual_payment(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $invitation = $this->draft(User::factory()->create());
        $this->post(route('invitations.checkout', $invitation));
        $payment = Payment::latest('id')->first();

        $this->actingAs($admin);
        foreach (['admin.dashboard', 'admin.payments.index', 'admin.invitations.index', 'admin.users.index', 'admin.packages.index', 'admin.packages.create', 'admin.themes.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('admin.packages.edit', Package::first()))->assertOk();

        $this->patch(route('admin.payments.mark-paid', $payment))->assertSessionHas('success');
        $this->assertTrue($invitation->fresh()->isActive());

        $this->patch(route('admin.invitations.status', $invitation), ['action' => 'suspend']);
        $this->assertFalse($invitation->fresh()->isActive());
    }
}
