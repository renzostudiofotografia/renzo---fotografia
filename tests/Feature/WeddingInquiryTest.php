<?php

namespace Tests\Feature;

use App\LandingContent;
use App\Models\LandingLead;
use App\Models\LandingPage;
use App\Models\User;
use App\Notifications\LandingInquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class WeddingInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** @param array<string, mixed> $overrides */
    private function landing(string $slug = 'consulta-boda', bool $published = true, array $overrides = []): LandingPage
    {
        $data = array_replace(app(LandingContent::class)->defaults('wedding'), $overrides);

        return LandingPage::create([
            'name' => 'Consulta de boda de prueba', 'slug' => $slug,
            'draft' => $data, 'published' => $published ? $data : null,
            'revision' => 1, 'published_at' => $published ? now() : null,
        ]);
    }

    /** @return array<string, mixed> */
    private function leadData(): array
    {
        return [
            'submission_id' => (string) Str::uuid(),
            'name' => 'Pareja de prueba', 'phone' => '+51 987 654 321', 'email' => 'pareja@example.test',
            'event_date' => now()->addYear()->toDateString(), 'location' => 'Arequipa, locación de prueba',
            'message' => 'Consulta utilizada únicamente en pruebas.', 'consent' => '1', 'company' => '',
        ];
    }

    public function test_successful_inquiry_snapshots_the_original_published_experience_index_and_campaign_attribution_once(): void
    {
        Notification::fake();
        config(['mail.default' => 'smtp']);
        $landing = $this->landing(overrides: [
            'require_event_date' => true, 'show_location' => true, 'require_location' => true,
            'show_experience' => true, 'notification_email' => 'administrador@example.test',
        ]);
        $data = $landing->published;
        $data['experiences'][0]['enabled'] = false;
        $data['experiences'][1]['name'] = 'Experiencia publicada seleccionada';
        $landing->update(['published' => $data]);
        $draft = $data;
        $draft['experiences'][1]['name'] = 'Nombre privado todavía sin publicar';
        $landing->update(['draft' => $draft]);
        $payload = array_replace($this->leadData(), [
            'experience' => '1', 'experience_name' => 'Nombre suministrado por el navegador',
            'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'bodas-prueba',
            'utm_content' => 'fotografia-a', 'utm_term' => 'arequipa', 'fbclid' => 'fb-prueba-123',
        ]);

        $response = $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertOk()
            ->assertJsonPath('created', true)->assertJsonPath('event_id', $payload['submission_id'])
            ->assertJsonPath('tracking.currency', 'PEN');
        $this->assertDatabaseCount('landing_leads', 1);
        $lead = LandingLead::firstOrFail();
        $this->assertSame($landing->id, $lead->landing_page_id);
        $this->assertSame('51987654321', $lead->phone);
        $this->assertSame($payload['event_date'], $lead->event_date->toDateString());
        $this->assertNotNull($lead->consent_at);
        $this->assertSame([
            'source' => 'landing', 'landing_slug' => $landing->slug,
            'location' => $payload['location'], 'experience' => 'Experiencia publicada seleccionada',
            'experience_index' => '1',
        ], $lead->inquiry_details);
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'] as $key) {
            $this->assertSame($payload[$key], $lead->{$key});
        }
        foreach (['Pareja de prueba', 'pareja@example.test', '51987654321', $payload['location']] as $privateValue) {
            $this->assertStringNotContainsString($privateValue, $response->getContent());
        }
        Notification::assertSentOnDemandOnce(LandingInquiryReceived::class);
        Notification::assertSentOnDemand(LandingInquiryReceived::class,
            fn (LandingInquiryReceived $notification, array $channels, object $notifiable): bool => $notification->landing->is($landing)
                && $channels === ['mail'] && $notifiable->routes['mail'] === 'administrador@example.test');

        $before = $lead->getRawOriginal();
        $retry = array_replace($payload, ['location' => 'Otra ubicación', 'experience' => '2']);
        $this->postJson('/lp/'.$landing->slug.'/leads', $retry)->assertOk()
            ->assertJsonPath('created', false)->assertJsonPath('event_id', $payload['submission_id']);
        $this->assertDatabaseCount('landing_leads', 1);
        $this->assertSame($before, $lead->fresh()->getRawOriginal());
        Notification::assertSentOnDemandOnce(LandingInquiryReceived::class);
    }

    public function test_required_wedding_details_past_dates_and_spam_are_rejected_without_creating_inquiries(): void
    {
        Notification::fake();
        $landing = $this->landing(overrides: ['require_event_date' => true, 'show_location' => true, 'require_location' => true]);

        foreach ([
            ['event_date', ''],
            ['location', ''],
            ['event_date', now()->subDay()->toDateString()],
            ['event_date', 'no-es-una-fecha'],
            ['company', 'Un robot completó este campo'],
            ['consent', '0'],
        ] as [$field, $invalid]) {
            $this->postJson('/lp/'.$landing->slug.'/leads', array_replace($this->leadData(), [$field => $invalid]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }

        $this->assertDatabaseCount('landing_leads', 0);
        Notification::assertNothingSent();
    }

    public function test_experience_choices_accept_only_original_indices_of_visible_published_experiences(): void
    {
        $landing = $this->landing();
        $data = $landing->published;
        $data['experiences'][0]['enabled'] = false;
        $landing->update(['published' => $data]);

        foreach (['0', '99', $data['experiences'][1]['name']] as $invalid) {
            $this->postJson('/lp/'.$landing->slug.'/leads', array_replace($this->leadData(), ['experience' => $invalid]))
                ->assertUnprocessable()->assertJsonValidationErrors('experience');
        }
        $this->assertDatabaseCount('landing_leads', 0);
        $this->postJson('/lp/'.$landing->slug.'/leads', array_replace($this->leadData(), ['experience' => '1']))
            ->assertOk()->assertJsonPath('created', true);
        $this->assertSame($data['experiences'][1]['name'], LandingLead::firstOrFail()->inquiry_details['experience']);
    }

    public function test_hidden_optional_fields_do_not_become_required_or_store_browser_supplied_values(): void
    {
        $landing = $this->landing(overrides: [
            'require_event_date' => false, 'show_location' => false, 'require_location' => true, 'show_experience' => false,
        ]);
        $payload = array_replace($this->leadData(), ['event_date' => '', 'location' => '', 'experience' => '1']);

        $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertOk()->assertJsonPath('created', true);
        $lead = LandingLead::firstOrFail();
        $this->assertNull($lead->event_date);
        $this->assertNull($lead->inquiry_details['location']);
        $this->assertNull($lead->inquiry_details['experience']);
        $this->assertNull($lead->inquiry_details['experience_index']);
    }

    public function test_notifications_are_skipped_without_an_active_delivery_channel_or_recipient(): void
    {
        foreach ([['log', 'administrador@example.test'], ['array', 'administrador@example.test'], ['smtp', '']] as $index => [$mailer, $recipient]) {
            Notification::fake();
            config(['mail.default' => $mailer]);
            $landing = $this->landing('consulta-sin-notificacion-'.$index, overrides: ['notification_email' => $recipient]);

            $this->postJson('/lp/'.$landing->slug.'/leads', $this->leadData())->assertOk()->assertJsonPath('created', true);
            Notification::assertNothingSent();
        }

        $this->assertDatabaseCount('landing_leads', 3);
    }

    public function test_a_notification_delivery_failure_does_not_lose_the_saved_inquiry_or_return_a_false_failure(): void
    {
        config(['mail.default' => 'smtp']);
        $landing = $this->landing(overrides: ['notification_email' => 'administrador@example.test']);
        Notification::shouldReceive('send')->once()
            ->withArgs(fn (object $notifiable, LandingInquiryReceived $notification): bool => $notifiable->routes['mail'] === 'administrador@example.test' && $notification->landing->is($landing))
            ->andThrow(new \RuntimeException('Fallo de entrega simulado en pruebas.'));
        $payload = $this->leadData();

        $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertOk()
            ->assertJsonPath('created', true)->assertJsonPath('event_id', $payload['submission_id']);
        $this->assertDatabaseHas('landing_leads', ['submission_id' => $payload['submission_id'], 'landing_page_id' => $landing->id]);
        $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertOk()->assertJsonPath('created', false);
        $this->assertDatabaseCount('landing_leads', 1);
    }

    public function test_renaming_a_slug_preserves_campaign_queries_and_accepts_inquiries_from_older_open_pages(): void
    {
        $landing = $this->landing('bodas-anterior');
        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'slug' => 'bodas-actual', 'revision' => 1, 'data' => $landing->draft,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame(['bodas-anterior'], $landing->previous_slugs);

        $response = $this->get('/lp/bodas-anterior?utm_campaign=bodas-peru&utm_source=facebook&fbclid=prueba-abc')
            ->assertStatus(301);
        $target = $response->headers->get('Location');
        $this->assertSame('/lp/bodas-actual', parse_url($target, PHP_URL_PATH));
        parse_str((string) parse_url($target, PHP_URL_QUERY), $query);
        $this->assertSame(['fbclid' => 'prueba-abc', 'utm_campaign' => 'bodas-peru', 'utm_source' => 'facebook'], $query);

        $payload = array_replace($this->leadData(), ['utm_source' => 'facebook', 'utm_campaign' => 'pagina-antigua']);
        $this->postJson('/lp/bodas-anterior/leads', $payload)->assertOk()->assertJsonPath('created', true);
        $lead = LandingLead::where('submission_id', $payload['submission_id'])->firstOrFail();
        $this->assertSame($landing->id, $lead->landing_page_id);
        $this->assertSame('bodas-actual', $lead->inquiry_details['landing_slug']);
        $this->assertSame('pagina-antigua', $lead->utm_campaign);
        $this->get('/lp/bodas-actual')->assertOk();

        $this->put('/admin/landings/'.$landing->id, [
            'name' => 'Nombre interno actualizado', 'revision' => $landing->revision, 'data' => $landing->draft,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('bodas-actual', $landing->fresh()->slug);
        $this->assertSame(['bodas-anterior'], $landing->fresh()->previous_slugs);
    }

    public function test_current_and_historical_slugs_cannot_be_claimed_by_other_campaigns_even_while_in_trash(): void
    {
        $reserved = $this->landing('ruta-vigente');
        $reserved->update(['previous_slugs' => ['ruta-anterior']]);
        $other = $this->landing('otra-campana', published: false);
        $this->actingAs($this->owner());

        foreach ([false, true] as $trashed) {
            if ($trashed) {
                $reserved->delete();
            }
            foreach (['ruta-vigente', 'ruta-anterior'] as $slug) {
                $this->post('/admin/landings', ['name' => 'Intento de reutilización', 'slug' => $slug, 'preset' => 'wedding'])
                    ->assertSessionHasErrors('slug');
                $this->put('/admin/landings/'.$other->id, [
                    'name' => $other->name, 'slug' => $slug, 'revision' => 1, 'data' => $other->draft,
                ])->assertSessionHasErrors('slug');
            }
        }

        $this->assertSame(2, LandingPage::withTrashed()->count());
        $this->assertSame('otra-campana', $other->fresh()->slug);
        $this->assertSame(1, $other->fresh()->revision);
    }

    public function test_sitemap_lists_only_the_current_urls_of_published_campaigns_outside_the_trash(): void
    {
        $published = $this->landing('boda-publicada');
        $published->update(['previous_slugs' => ['boda-antigua']]);
        $this->landing('boda-borrador', published: false);
        $trashed = $this->landing('boda-papelera');
        $trashed->delete();

        $response = $this->get('/landings-sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/lp/boda-publicada')->assertDontSee('/lp/boda-antigua')
            ->assertDontSee('/lp/boda-borrador')->assertDontSee('/lp/boda-papelera');
        $document = new \DOMDocument;
        $this->assertTrue($document->loadXML($response->getContent()));
        $this->assertCount(1, $document->getElementsByTagName('url'));
        $this->get('/lp/boda-papelera')->assertNotFound();
        $this->postJson('/lp/boda-papelera/leads', $this->leadData())->assertNotFound();
        $this->assertDatabaseCount('landing_leads', 0);
    }

    public function test_operational_command_creates_a_private_wedding_draft_and_rejects_reserved_or_invalid_slugs(): void
    {
        $this->artisan('landing:wedding', ['slug' => 'comando-nueva-boda', '--create' => true])->assertSuccessful();
        $created = LandingPage::where('slug', 'comando-nueva-boda')->firstOrFail();
        $this->assertSame('wedding', $created->draft['visual_theme']);
        $this->assertNull($created->published);
        $this->assertSame(1, $created->revision);
        $this->get('/lp/comando-nueva-boda')->assertNotFound();

        $reserved = $this->landing('comando-reservado');
        $reserved->update(['previous_slugs' => ['comando-anterior']]);
        $reserved->delete();
        foreach (['comando-reservado', 'comando-anterior', '../admin'] as $slug) {
            $this->artisan('landing:wedding', ['slug' => $slug, '--create' => true])->assertFailed();
        }
        $this->artisan('landing:wedding', ['slug' => 'no-existe-sin-create'])->assertFailed();
        $this->assertSame(2, LandingPage::withTrashed()->count());
    }

    public function test_operational_command_backs_up_existing_content_and_prepares_only_its_draft(): void
    {
        $slug = 'comando-respaldo-'.strtolower(Str::random(10));
        $original = config('landing.defaults');
        $original['sections'][0]['title'] = 'Portada existente que debe permanecer';
        $original['page_title'] = 'SEO conservado en la operación';
        $original['hero_video_url'] = 'https://example.test/video-conservado.mp4';
        $landing = LandingPage::create([
            'name' => 'Campaña existente de prueba', 'slug' => $slug,
            'draft' => $original, 'published' => $original, 'revision' => 4, 'published_at' => now(),
        ])->fresh();
        $rawBefore = $landing->getAttributes();
        $backupPattern = storage_path('app/landing-backups/'.$slug.'-*.json');

        try {
            $this->artisan('landing:wedding', ['slug' => $slug])->assertSuccessful();
            $landing->refresh();
            $backups = File::glob($backupPattern);
            $this->assertCount(1, $backups);
            $this->assertSame($rawBefore, json_decode(File::get($backups[0]), true, flags: JSON_THROW_ON_ERROR));
            $this->assertSame($original, $landing->published);
            $this->assertSame($rawBefore['published_at'], $landing->getRawOriginal('published_at'));
            $this->assertSame('Campaña existente de prueba', $landing->name);
            $this->assertSame(5, $landing->revision);
            $this->assertSame('wedding', $landing->draft['visual_theme']);
            $this->assertSame($original['page_title'], $landing->draft['page_title']);
            $this->assertSame($original['hero_video_url'], $landing->draft['hero_video_url']);
            $this->assertSame($original['sections'][0]['title'], collect($landing->draft['sections'])->firstWhere('type', 'hero')['title']);
        } finally {
            File::delete(File::glob($backupPattern));
        }
    }

    public function test_truncated_editor_payloads_preserve_saved_content_and_complete_json_saves_return_the_editor_destination(): void
    {
        $landing = $this->landing();
        $before = $landing->fresh()->getRawOriginal();
        $edited = array_replace($landing->draft, ['page_title' => 'Nuevo SEO todavía en borrador']);
        $this->actingAs($this->owner());

        $this->putJson('/admin/landings/'.$landing->id, [
            'editor_form' => '1', 'name' => $landing->name, 'revision' => 1, 'data' => $edited,
        ])->assertUnprocessable()->assertJsonValidationErrors('data');
        $this->assertSame($before, $landing->fresh()->getRawOriginal());

        $this->putJson('/admin/landings/'.$landing->id, [
            'editor_form' => '1', 'editor_complete' => '1', 'name' => $landing->name,
            'revision' => 1, 'data' => $edited,
        ])->assertOk()->assertJsonPath('redirect', route('admin.landings.edit', $landing));
        $this->assertSame('Nuevo SEO todavía en borrador', $landing->fresh()->draft['page_title']);
        $this->assertSame($before['published'], $landing->fresh()->getRawOriginal('published'));
        $this->assertSame(2, $landing->fresh()->revision);
    }
}
