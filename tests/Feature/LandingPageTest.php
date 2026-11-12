<?php

namespace Tests\Feature;

use App\Models\LandingLead;
use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('LANDING_CAPTURE')) {
            URL::forceRootUrl('http://127.0.0.1:8010');
        }
    }

    private function owner(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function capture(string $name, string $html): void
    {
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            file_put_contents(storage_path('app/visual-review/'.$name), $html);
        }
    }

    /** @param array<string, mixed> $overrides */
    private function landing(bool $published = false, string $slug = 'campana-bodas', array $overrides = []): LandingPage
    {
        $data = array_replace(config('landing.defaults'), $overrides);

        return LandingPage::create([
            'name' => 'Campaña de bodas',
            'slug' => $slug,
            'draft' => $data,
            'published' => $published ? $data : null,
            'revision' => 1,
            'published_at' => $published ? now() : null,
        ]);
    }

    /** @return array<string, mixed> */
    private function leadData(): array
    {
        return [
            'submission_id' => (string) Str::uuid(),
            'name' => 'María de prueba',
            'phone' => '+51 987 654 321',
            'email' => 'maria@example.test',
            'event_date' => now()->addYear()->toDateString(),
            'message' => 'Quisiera conocer la disponibilidad para mi boda.',
            'consent' => '1',
            'company' => '',
        ];
    }

    public function test_campaign_management_requires_an_authenticated_administrator(): void
    {
        $landing = $this->landing();
        $this->get('/admin/landings')->assertRedirect('/admin/login');
        $this->post('/admin/landings', [])->assertRedirect('/admin/login');
        $this->get('/admin/landings/'.$landing->id.'/preview')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create());
        foreach ([
            '/admin/landings',
            '/admin/landings/'.$landing->id.'/edit',
            '/admin/landings/'.$landing->id.'/preview',
            '/admin/landings/'.$landing->id.'/leads',
        ] as $path) {
            $this->get($path)->assertForbidden();
        }
        $this->post('/admin/landings', [])->assertForbidden();
        $this->put('/admin/landings/'.$landing->id, [])->assertForbidden();
        $this->post('/admin/landings/'.$landing->id.'/preview', [])->assertForbidden();
        $this->post('/admin/landings/'.$landing->id.'/publish', [])->assertForbidden();
        $this->post('/admin/landings/'.$landing->id.'/unpublish', [])->assertForbidden();
    }

    public function test_owner_can_create_multiple_draft_campaigns_and_open_their_editors(): void
    {
        $this->actingAs($this->owner());
        foreach (['editorial', 'modern'] as $preset) {
            $this->post('/admin/landings', [
                'name' => 'Campaña '.$preset,
                'slug' => 'bodas-'.$preset,
                'preset' => $preset,
            ])->assertSessionHasNoErrors()->assertRedirect();

            $landing = LandingPage::where('slug', 'bodas-'.$preset)->firstOrFail();
            $this->assertNull($landing->published);
            $editor = $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk();
            if ($preset === 'editorial') {
                $this->capture('landing-editor.html', $editor->getContent());
            }
            $this->get('/lp/'.$landing->slug)->assertNotFound();
        }
        $list = $this->get('/admin/landings')->assertOk()->assertSee('Campaña editorial')->assertSee('Campaña modern');
        $this->capture('landings.html', $list->getContent());
        $this->assertDatabaseCount('landing_pages', 2);
    }

    public function test_duplicate_slugs_and_unsafe_campaign_paths_are_rejected(): void
    {
        $this->landing();
        $this->actingAs($this->owner());
        foreach (['campana-bodas', '../admin', 'https://example.test/campana'] as $slug) {
            $this->post('/admin/landings', [
                'name' => 'Otra campaña', 'slug' => $slug, 'preset' => 'editorial',
            ])->assertSessionHasErrors('slug');
        }
        $this->assertDatabaseCount('landing_pages', 1);
    }

    public function test_unpublished_campaigns_do_not_accept_or_reveal_leads(): void
    {
        $landing = $this->landing();
        $this->get('/lp/'.$landing->slug)->assertNotFound();
        $this->postJson('/lp/'.$landing->slug.'/leads', $this->leadData())->assertNotFound();
        $this->assertDatabaseCount('landing_leads', 0);
    }

    public function test_drafts_remain_private_and_each_campaign_is_published_independently(): void
    {
        $landing = $this->landing(overrides: ['page_title' => 'Versión privada de la campaña']);
        $this->actingAs($this->owner());
        $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk()->assertSee('Versión privada de la campaña');
        $this->get('/lp/'.$landing->slug.'?editor_preview=1')->assertNotFound();

        $this->post('/admin/publish')->assertSessionHasNoErrors();
        $this->assertNull($landing->fresh()->published);
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
            ->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertNotNull($landing->published_at);
        $this->get('/lp/'.$landing->slug)->assertOk()->assertSee('Versión privada de la campaña');

        $draft = array_replace($landing->draft, ['page_title' => 'Nuevo borrador todavía privado']);
        $this->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => $landing->revision, 'data' => $draft,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/lp/'.$landing->slug)->assertOk()->assertSee('Versión privada de la campaña')->assertDontSee('Nuevo borrador todavía privado');
        $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk()->assertSee('Nuevo borrador todavía privado');
        $this->assertSame('Nuevo borrador todavía privado', $landing->fresh()->draft['page_title']);
        $this->assertSame('Versión privada de la campaña', $landing->fresh()->published['page_title']);
    }

    public function test_stale_revisions_cannot_save_publish_or_unpublish_a_campaign(): void
    {
        $landing = $this->landing(published: true);
        $landing->update(['revision' => 2]);
        $this->actingAs($this->owner());
        $this->put('/admin/landings/'.$landing->id, [
            'name' => 'Cambio desde otra ventana', 'revision' => 1, 'data' => $landing->draft,
        ])->assertSessionHasErrors('revision');
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => 1])->assertSessionHasErrors('revision');
        $this->post('/admin/landings/'.$landing->id.'/unpublish', ['revision' => 1])->assertSessionHasErrors('revision');
        $this->assertSame(2, $landing->fresh()->revision);
        $this->assertSame('Campaña de bodas', $landing->fresh()->name);
        $this->assertNotNull($landing->fresh()->published);

        $this->post('/admin/landings/'.$landing->id.'/unpublish', ['revision' => 2])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(3, $landing->fresh()->revision);
        $this->get('/lp/'.$landing->slug)->assertNotFound();
        $this->postJson('/lp/'.$landing->slug.'/leads', $this->leadData())->assertNotFound();
    }

    public function test_campaign_cannot_be_published_without_one_visible_cover_and_contact_form(): void
    {
        $landing = $this->landing();
        $sections = $landing->draft['sections'];
        $this->actingAs($this->owner());
        foreach ([
            array_values(array_filter($sections, fn (array $section): bool => $section['type'] !== 'hero')),
            array_map(fn (array $section): array => array_replace($section, ['enabled' => $section['type'] !== 'lead']), $sections),
            [...$sections, $sections[0]],
        ] as $invalidSections) {
            $landing->update(['draft' => array_replace($landing->draft, ['sections' => $invalidSections])]);
            $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
                ->assertSessionHasErrors('data.sections');
            $this->assertNull($landing->fresh()->published);
        }
    }

    public function test_form_encoded_values_are_accepted_and_removing_all_optional_repeater_items_persists_empty_lists(): void
    {
        $landing = $this->landing();
        $data = $landing->draft;
        unset($data['benefits'], $data['gallery'], $data['testimonials'], $data['faqs']);
        array_walk_recursive($data, static function (mixed &$value): void {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_int($value) || is_float($value)) {
                $value = (string) $value;
            }
        });

        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => (string) $landing->revision, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        foreach (['benefits', 'gallery', 'testimonials', 'faqs'] as $key) {
            $this->assertSame([], $landing->draft[$key]);
        }
        $this->assertTrue((bool) $landing->draft['sections'][0]['enabled']);
        $this->assertSame(72, (int) $landing->draft['heading_size']);
    }

    public function test_failed_save_keeps_removed_gallery_items_and_the_submitted_revision_in_the_editor(): void
    {
        $landing = $this->landing();
        $landing->update(['revision' => 3]);
        $data = $landing->draft;
        unset($data['gallery']);
        $data['background_color'] = 'color-invalido';
        $editorUrl = route('admin.landings.edit', $landing);

        $this->actingAs($this->owner())->from($editorUrl)->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => '2', 'data' => $data,
        ])->assertRedirect($editorUrl)->assertSessionHasErrors('data.background_color');

        $this->get($editorUrl)->assertOk()
            ->assertViewHas('data', fn (array $editorData): bool => $editorData['gallery'] === [] && $editorData['background_color'] === 'color-invalido')
            ->assertViewHas('revision', '2')
            ->assertSee('name="revision" value="2"', false);
        $this->assertSame(3, $landing->fresh()->revision);
        $this->assertSame($landing->draft, $landing->fresh()->draft);
    }

    public function test_editor_rejects_unsafe_urls_styles_fonts_pixel_ids_and_unrecognized_nested_keys(): void
    {
        $landing = $this->landing();
        $this->actingAs($this->owner());
        $unsafeSection = $landing->draft['sections'][0];
        $unsafeSection['button_url'] = 'javascript:alert(document.cookie)';
        $extraSection = $landing->draft['sections'][0];
        $extraSection['unvalidated_html'] = '<script>alert(1)</script>';
        foreach ([
            ['sections' => [$unsafeSection]],
            ['sections' => [$extraSection]],
            ['heading_font' => 'Untrusted Font'],
            ['background_color' => '#fff; background-image:url(https://example.test)'],
            ['pixel_id' => '12345<script>alert(1)</script>'],
            ['price' => -100],
            ['unexpected_script' => '<script>alert(1)</script>'],
        ] as $invalid) {
            $this->put('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => $landing->revision,
                'data' => array_replace($landing->draft, $invalid),
            ])->assertSessionHasErrors();
        }
        $this->assertSame($landing->draft, $landing->fresh()->draft);
        $this->assertSame(1, $landing->fresh()->revision);
    }

    public function test_live_preview_renders_unsaved_changes_without_tracking_or_mutating_the_campaign(): void
    {
        $landing = $this->landing(published: true, overrides: ['pixel_id' => '123456789012345']);
        $landing->refresh();
        $before = $landing->getRawOriginal();
        $previewData = $landing->draft;
        $previewData['sections'][0]['title'] = 'Un cambio visible antes de guardar';
        $previewData['heading_size'] = 86;
        $this->actingAs($this->owner());
        foreach ([
            $this->get('/admin/landings/'.$landing->id.'/preview'),
            $this->postJson('/admin/landings/'.$landing->id.'/preview', ['data' => $previewData]),
        ] as $response) {
            $response->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertDontSee('connect.facebook.net', false)->assertDontSee('fbevents.js', false);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $response->assertSee('Un cambio visible antes de guardar');
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
        $this->get('/lp/'.$landing->slug)->assertDontSee('Un cambio visible antes de guardar');
        $this->assertDatabaseCount('landing_leads', 0);
    }

    public function test_saved_text_is_escaped_when_the_landing_is_rendered(): void
    {
        $draft = config('landing.defaults');
        $draft['sections'][0]['title'] = '<img src=x onerror=alert(1)>';
        $landing = $this->landing(published: true, overrides: $draft);
        $this->get('/lp/'.$landing->slug)->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_confirmed_lead_is_saved_with_attribution_and_only_non_contact_tracking_data_is_returned(): void
    {
        $landing = $this->landing(published: true);
        $public = $this->get('/lp/'.$landing->slug)->assertOk();
        $this->capture('landing-public.html', $public->getContent());
        $payload = [...$this->leadData(),
            'utm_source' => 'facebook', 'utm_medium' => 'paid_social',
            'utm_campaign' => 'bodas_2027', 'utm_content' => 'fotografia_a',
            'utm_term' => 'bodas_lima', 'fbclid' => 'facebook-click-de-prueba',
        ];
        $response = $this->postJson('/lp/'.$landing->slug.'/leads', $payload)
            ->assertSuccessful()
            ->assertJsonPath('created', true)
            ->assertJsonPath('tracking.currency', 'PEN')
            ->assertJsonStructure(['message', 'event_id', 'created', 'tracking']);

        $this->assertDatabaseHas('landing_leads', [
            'landing_page_id' => $landing->id,
            'submission_id' => $payload['submission_id'],
            'name' => $payload['name'], 'phone' => '51987654321',
            'email' => $payload['email'],
            'message' => $payload['message'], 'utm_source' => 'facebook',
            'utm_medium' => 'paid_social', 'utm_campaign' => 'bodas_2027',
            'utm_content' => 'fotografia_a', 'utm_term' => 'bodas_lima',
            'fbclid' => 'facebook-click-de-prueba',
        ]);
        $this->assertNotNull(LandingLead::firstOrFail()->consent_at);
        $this->assertSame($payload['event_date'], LandingLead::firstOrFail()->event_date->toDateString());
        foreach (['name', 'phone', 'email', 'message'] as $key) {
            $this->assertStringNotContainsString($payload[$key], $response->getContent());
        }
    }

    public function test_retrying_a_submission_is_idempotent_and_does_not_repeat_a_conversion(): void
    {
        $landing = $this->landing(published: true);
        $payload = $this->leadData();
        $first = $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertSuccessful()->assertJsonPath('created', true);
        $second = $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertSuccessful()->assertJsonPath('created', false);

        $this->assertSame($first->json('event_id'), $second->json('event_id'));
        $this->assertDatabaseCount('landing_leads', 1);
    }

    public function test_lead_tracking_uses_the_published_pixel_and_value_with_currency_fixed_to_soles(): void
    {
        $landing = $this->landing(published: true, overrides: [
            'pixel_id' => '123456789012345', 'lead_value' => 75.5, 'track_lead' => true,
        ]);
        $draft = $landing->draft;
        $draft['pixel_id'] = '987654321098765';
        $draft['lead_value'] = 250;
        $landing->update(['draft' => $draft]);

        $this->postJson('/lp/'.$landing->slug.'/leads', $this->leadData())
            ->assertSuccessful()
            ->assertJsonPath('tracking.pixel_id', '123456789012345')
            ->assertJsonPath('tracking.lead', true)
            ->assertJsonPath('tracking.value', 75.5)
            ->assertJsonPath('tracking.currency', 'PEN');
    }

    public function test_submission_identifier_cannot_be_reused_for_another_campaign(): void
    {
        $first = $this->landing(published: true);
        $second = $this->landing(published: true, slug: 'otra-campana');
        $payload = $this->leadData();
        $this->postJson('/lp/'.$first->slug.'/leads', $payload)->assertSuccessful();
        $this->postJson('/lp/'.$second->slug.'/leads', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('landing_leads', 1);
    }

    public function test_invalid_forms_honeypots_and_missing_consent_never_create_leads(): void
    {
        $landing = $this->landing(published: true);
        foreach ([
            ['name' => ''], ['phone' => ''], ['email' => 'correo-invalido'],
            ['consent' => '0'], ['company' => 'spam automatizado'],
            ['submission_id' => 'identificador-invalido'], ['event_date' => 'fecha-invalida'],
        ] as $invalid) {
            $this->postJson('/lp/'.$landing->slug.'/leads', array_replace($this->leadData(), $invalid))
                ->assertUnprocessable();
        }
        $this->assertDatabaseCount('landing_leads', 0);
    }

    public function test_optional_lead_fields_can_be_omitted_and_campaign_contacts_are_private(): void
    {
        $landing = $this->landing(published: true);
        $payload = $this->leadData();
        unset($payload['email'], $payload['event_date'], $payload['message']);
        $this->postJson('/lp/'.$landing->slug.'/leads', $payload)->assertSuccessful();
        $this->get('/admin/landings/'.$landing->id.'/leads')->assertRedirect('/admin/login');
        $this->actingAs($this->owner())->get('/admin/landings/'.$landing->id.'/leads')
            ->assertOk()->assertSee($payload['name'])->assertSee('51987654321');
    }

    public function test_each_campaign_only_lists_its_own_contacts(): void
    {
        $first = $this->landing(published: true);
        $second = $this->landing(published: true, slug: 'campana-prebodas');
        $this->postJson('/lp/'.$first->slug.'/leads', array_replace($this->leadData(), ['name' => 'Contacto primera campaña']))
            ->assertSuccessful();
        $this->postJson('/lp/'.$second->slug.'/leads', array_replace($this->leadData(), ['name' => 'Contacto segunda campaña']))
            ->assertSuccessful();

        $this->actingAs($this->owner());
        $this->get('/admin/landings/'.$first->id.'/leads')->assertOk()
            ->assertSee('Contacto primera campaña')->assertDontSee('Contacto segunda campaña');
        $this->get('/admin/landings/'.$second->id.'/leads')->assertOk()
            ->assertSee('Contacto segunda campaña')->assertDontSee('Contacto primera campaña');
    }
}
