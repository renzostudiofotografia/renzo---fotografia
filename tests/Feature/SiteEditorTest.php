<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SiteEditorTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['is_admin' => true, 'name' => 'Propietario de prueba']);
    }

    /** @return array<string, mixed> */
    private function sectionData(string $section): array
    {
        $data = config('editor.sections.'.$section.'.defaults');
        unset($data['position']);

        return $data;
    }

    public function test_panel_requires_an_owner_and_regular_users_cannot_publish(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->put('/admin/editor/home_hero', [])->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->post('/admin/publish')->assertForbidden();
    }

    public function test_first_owner_requires_a_valid_signed_setup_link_and_cannot_be_created_twice(): void
    {
        $this->get('/admin/setup')->assertForbidden();
        $url = URL::temporarySignedRoute('admin.setup', now()->addMinutes(10));
        $this->get($url)->assertOk()->assertSee('Hagámoslo tuyo.');
        $this->post($url, [
            'name' => 'Propietario', 'email' => 'owner@example.test',
            'password' => 'UnaClaveSegura2026', 'password_confirmation' => 'UnaClaveSegura2026',
        ])->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertTrue(User::first()->is_admin);
        $this->get($url)->assertForbidden();
    }

    public function test_admin_can_login_and_logout_but_a_regular_account_cannot_login(): void
    {
        $owner = User::factory()->create(['is_admin' => true, 'password' => 'ClavePrueba12345']);
        $this->post('/admin/login', ['email' => $owner->email, 'password' => 'ClavePrueba12345'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($owner);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
        $regular = User::factory()->create(['password' => 'ClavePrueba12345']);
        $this->post('/admin/login', ['email' => $regular->email, 'password' => 'ClavePrueba12345'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_all_editor_sections_and_public_pages_render(): void
    {
        $this->actingAs($this->owner());
        $dashboard = $this->get('/admin')->assertOk()->assertSee('Tu estudio,');
        if (getenv('SITE_EDITOR_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            file_put_contents(storage_path('app/visual-review/dashboard.html'), $dashboard->getContent());
        }
        $this->get('/admin/media')->assertOk();
        $this->get('/admin/integrations')->assertOk();
        foreach (array_keys(config('editor.sections')) as $section) {
            $editor = $this->get('/admin/editor/'.$section)->assertOk()->assertSee('Guardar borrador');
            $editor->assertSee(route('admin.edit', 'home_hero'))->assertSee('class="section-link active"', false);
            if (getenv('SITE_EDITOR_CAPTURE') && in_array($section, ['home_hero', 'typography', 'booking'], true)) {
                file_put_contents(storage_path('app/visual-review/'.$section.'.html'), $editor->getContent());
            }
        }
        foreach (['/', '/sobre-mi', '/portafolio', '/servicios', '/servicios/cobertura-boda', '/servicios/preboda', '/servicios/postboda', '/contacto', '/citas'] as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_drafts_are_private_and_only_saved_content_is_published(): void
    {
        $owner = $this->owner();
        $data = $this->sectionData('home_hero');
        $data['phrases'] = "Nuestra nueva historia\nUn recuerdo inolvidable";
        $this->actingAs($owner)->put('/admin/editor/home_hero', ['revision' => 0, 'data' => $data])->assertSessionHasNoErrors();
        $this->get('/?editor_preview=1')->assertSee('Nuestra nueva historia')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/')->assertDontSee('Nuestra nueva historia');
        $this->post('/admin/publish')->assertSessionHasNoErrors();
        $this->get('/')->assertSee('Nuestra nueva historia');
        $this->assertSame(['Nuestra nueva historia', 'Un recuerdo inolvidable'], SiteSetting::first()->published['phrases']);
    }

    public function test_section_navigation_opens_the_selected_form_and_targets_its_preview(): void
    {
        $this->actingAs($this->owner());
        $document = new \DOMDocument;
        $response = $this->get('/admin/editor/home_hero')->assertOk();
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $xpath = new \DOMXPath($document);

        foreach (config('editor.sections') as $section => $definition) {
            $editorUrl = route('admin.edit', ['section' => $section]);
            $links = $xpath->query('//a[@href="'.$editorUrl.'"][contains(concat(" ", normalize-space(@class), " "), " section-link ")]');
            $this->assertCount(1, $links, 'Missing unique editor link for '.$section);
            $this->assertStringContainsString($definition['label'], $links->item(0)->textContent);

            $editor = $this->get($links->item(0)->getAttribute('href'))->assertOk()
                ->assertViewHas('section', $section)->assertViewHas('definition', $definition);
            $selectedDocument = new \DOMDocument;
            $selectedDocument->loadHTML($editor->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
            $selectedXpath = new \DOMXPath($selectedDocument);
            $this->assertSame($editorUrl, $selectedXpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " section-link active ")]')->item(0)->getAttribute('href'));
            $this->assertSame(route('admin.save', ['section' => $section]), $selectedXpath->query('//form[@id="section-form"]')->item(0)->getAttribute('action'));
            $previewUrl = $selectedXpath->query('//iframe[@id="site-preview"]')->item(0)->getAttribute('src');
            parse_str(parse_url($previewUrl, PHP_URL_QUERY), $query);
            $expectedTarget = match ($section) {
                'portfolio_items' => 'portfolio_portfolio_page_section',
                'testimonials' => 'home_testimonials',
                default => $section,
            };
            $this->assertSame($expectedTarget, $query['editor_section'] ?? null);
        }
    }

    public function test_selected_hidden_section_can_be_previewed_without_becoming_public(): void
    {
        SiteSetting::create([
            'section' => 'home_experience',
            'draft' => ['enabled' => false],
            'published' => ['enabled' => false],
        ]);
        $this->actingAs($this->owner());
        $this->get('/?editor_preview=1')->assertDontSee('data-editor-section="home_experience"', false);
        $this->get('/?editor_preview=1&editor_section=home_experience')
            ->assertSee('data-editor-section="home_experience"', false);
        $this->get('/')->assertDontSee('data-editor-section="home_experience"', false);
        $this->post('/admin/logout');
        $this->get('/?editor_preview=1&editor_section=home_experience')->assertDontSee('data-editor-section="home_experience"', false);
    }

    public function test_guests_cannot_view_unpublished_drafts_with_a_preview_parameter(): void
    {
        SiteSetting::create(['section' => 'home_hero', 'draft' => ['phrases' => ['Frase secreta del borrador']], 'published' => null]);
        $this->get('/?editor_preview=1')->assertOk()->assertDontSee('Frase secreta del borrador');
    }

    public function test_conflicting_editor_revisions_do_not_overwrite_saved_content(): void
    {
        $data = $this->sectionData('home_hero');
        $this->actingAs($this->owner())->put('/admin/editor/home_hero', ['revision' => 0, 'data' => $data])->assertSessionHasNoErrors();
        $data['field_2'] = 'Un cambio desde otra ventana';
        $this->put('/admin/editor/home_hero', ['revision' => 0, 'data' => $data])->assertSessionHasErrors('revision');
        $this->assertNotSame($data['field_2'], SiteSetting::first()->draft['field_2']);
    }

    public function test_typography_calendly_and_nested_image_fields_reject_unsafe_values(): void
    {
        $this->actingAs($this->owner());
        $typography = $this->sectionData('typography');
        $typography['heading_font'] = 'Unknown font';
        $typography['hero_color'] = 'red; background:url(https://example.test)';
        $this->put('/admin/editor/typography', ['revision' => 0, 'data' => $typography])->assertSessionHasErrors(['data.heading_font', 'data.hero_color']);
        $booking = $this->sectionData('booking');
        $booking['url'] = 'https://calendly.com.attacker.test/event';
        $this->put('/admin/editor/booking', ['revision' => 0, 'data' => $booking])->assertSessionHasErrors('data.url');
        $hero = $this->sectionData('home_hero');
        $hero['images'][0]['image'] = "javascript:alert('bad')";
        $this->put('/admin/editor/home_hero', ['revision' => 0, 'data' => $hero])->assertSessionHasErrors('data.images.0.image');
    }

    public function test_reordering_visibility_and_discard_only_affect_drafts_until_publication(): void
    {
        $this->actingAs($this->owner())->post('/admin/editor/home_hero/reorder', ['direction' => 'down'])->assertSessionHasNoErrors();
        $this->assertSame(1, SiteSetting::where('section', 'home_hero')->first()->draft['position']);
        $data = $this->sectionData('home_hero');
        $data['enabled'] = false;
        $setting = SiteSetting::where('section', 'home_hero')->first();
        $this->put('/admin/editor/home_hero', ['revision' => $setting->revision, 'data' => $data])->assertSessionHasNoErrors();
        $this->get('/?editor_preview=1')->assertDontSee('class="hero"', false);
        $this->get('/')->assertSee('class="hero"', false);
        $this->post('/admin/editor/home_hero/discard')->assertSessionHasNoErrors();
        $this->assertTrue(SiteSetting::where('section', 'home_hero')->first()->draft['enabled']);
    }

    public function test_uploaded_photographs_are_reusable_and_invalid_files_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs($this->owner());
        $image = UploadedFile::fake()->createWithContent('boda.webp', file_get_contents(public_path('assets/images/hero/hero-01.webp')));
        $this->post('/admin/media', ['image' => $image, 'alt' => 'Pareja en su boda'])->assertSessionHasNoErrors();
        $asset = MediaAsset::firstOrFail();
        Storage::disk('local')->assertExists($asset->path);
        $this->get('/media/'.$asset->id)->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->post('/admin/media', ['image' => UploadedFile::fake()->createWithContent('bad.svg', '<svg onload="alert(1)"></svg>')])->assertSessionHasErrors('image');
    }

    public function test_cloudflare_uses_server_credentials_and_stable_media_urls(): void
    {
        Storage::fake('local');
        config(['services.cloudflare.account_id' => 'test-account', 'services.cloudflare.token' => 'private-test-token']);
        Http::preventStrayRequests();
        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true, 'result' => ['id' => 'photo-id', 'variants' => ['https://imagedelivery.net/account/photo-id/public']]])]);
        $this->actingAs($this->owner());
        $image = UploadedFile::fake()->createWithContent('boda.webp', file_get_contents(public_path('assets/images/hero/hero-01.webp')));
        $this->post('/admin/media', ['image' => $image])->assertSessionHasNoErrors();
        $asset = MediaAsset::firstOrFail();
        $this->assertSame('cloudflare', $asset->provider);
        $this->get('/media/'.$asset->id)->assertRedirect('https://imagedelivery.net/account/photo-id/public');
        $this->get('/admin/integrations')->assertDontSee('private-test-token');
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer private-test-token'));
    }

    public function test_r2_upload_can_be_retried_without_changing_the_library_link(): void
    {
        Storage::fake('local');
        $accountId = str_repeat('a', 32);
        config([
            'services.cloudflare_r2.account_id' => $accountId,
            'services.cloudflare_r2.bucket' => 'renzo-fotos',
            'services.cloudflare_r2.access_key_id' => 'exampleaccesskey',
            'services.cloudflare_r2.secret_access_key' => 'example-secret-key',
            'services.cloudflare_r2.public_url' => 'https://fotos.example.test',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            $accountId.'.r2.cloudflarestorage.com/renzo-fotos/site-media/*' => Http::sequence()->pushStatus(503)->pushStatus(200),
        ]);

        $this->actingAs($this->owner());
        $image = UploadedFile::fake()->createWithContent('boda.webp', file_get_contents(public_path('assets/images/hero/hero-01.webp')));
        $this->post('/admin/media', ['image' => $image])->assertSessionHasErrors('image');
        $asset = MediaAsset::firstOrFail();
        $this->assertSame('local', $asset->provider);
        $this->get('/media/'.$asset->id)->assertOk();
        $this->get('/admin/media')->assertSee('Enviar a R2');

        $this->from('/admin/media')->post('/admin/media/'.$asset->id.'/r2')->assertSessionHasNoErrors();
        $this->assertSame('r2', $asset->fresh()->provider);
        Storage::disk('local')->assertExists($asset->path);
        $this->get('/media/'.$asset->id)->assertRedirect('https://fotos.example.test/'.$asset->path);
        config(['services.cloudflare_r2.optimize' => true]);
        $this->get('/media/'.$asset->id)->assertRedirect(
            'https://fotos.example.test/cdn-cgi/image/width=1920,fit=scale-down,format=auto,onerror=redirect/'.$asset->path,
        );
        $this->get('/admin/integrations')->assertDontSee('example-secret-key');
        Http::assertSentCount(2);
    }

    public function test_media_metadata_accepts_blank_descriptions_without_losing_the_image(): void
    {
        Storage::fake('local');
        $this->actingAs($this->owner());
        $image = UploadedFile::fake()->createWithContent('boda.webp', file_get_contents(public_path('assets/images/hero/hero-01.webp')));
        $this->post('/admin/media', ['image' => $image, 'alt' => 'Pareja en su boda'])->assertSessionHasNoErrors();
        $asset = MediaAsset::firstOrFail();
        $originalPath = $asset->path;

        foreach (['', '   ', null] as $description) {
            $this->from('/admin/media')->patch('/admin/media/'.$asset->id, ['name' => 'Fiesta', 'alt' => $description])
                ->assertRedirect('/admin/media')->assertSessionHasNoErrors();
            $asset->refresh();
            $this->assertSame('Fiesta', $asset->name);
            $this->assertSame('', $asset->alt);
            $this->assertSame($originalPath, $asset->path);
            Storage::disk('local')->assertExists($originalPath);
        }

        $this->patch('/admin/media/'.$asset->id, ['name' => 'Fiesta', 'alt' => 'Pareja celebrando'])->assertSessionHasNoErrors();
        $this->assertSame('Pareja celebrando', $asset->fresh()->alt);
        $this->patch('/admin/media/'.$asset->id, ['name' => 'Boda'])->assertSessionHasNoErrors();
        $this->assertSame('Pareja celebrando', $asset->fresh()->alt);
        $this->assertSame(1, MediaAsset::count());
    }

    public function test_calendar_embed_is_only_shown_after_a_valid_link_is_published(): void
    {
        $this->actingAs($this->owner());
        $this->get('/citas')->assertOk()->assertDontSee('calendly-inline-widget');
        $booking = $this->sectionData('booking');
        $booking['url'] = 'https://calendly.com/renzo/conversemos';
        $this->put('/admin/editor/booking', ['revision' => 0, 'data' => $booking])->assertSessionHasNoErrors();
        $this->get('/citas')->assertDontSee('calendly-inline-widget');
        $this->post('/admin/publish')->assertSessionHasNoErrors();
        $this->get('/citas')->assertSee('calendly-inline-widget')->assertSee($booking['url']);
    }

    public function test_section_design_and_typography_are_applied_after_publishing(): void
    {
        $this->actingAs($this->owner());
        $design = $this->sectionData('home_experience');
        $design['custom_background'] = true;
        $design['section_background'] = '#ebe6da';
        $design['custom_spacing'] = true;
        $design['padding_top'] = 90;
        $design['padding_bottom'] = 70;
        $this->put('/admin/editor/home_experience', ['revision' => 0, 'data' => $design])->assertSessionHasNoErrors();
        $typography = $this->sectionData('typography');
        $typography['heading_font'] = 'Lora';
        $typography['base_size'] = 18;
        $this->put('/admin/editor/typography', ['revision' => 0, 'data' => $typography])->assertSessionHasNoErrors();
        $this->post('/admin/publish')->assertSessionHasNoErrors();
        $this->get('/')->assertSee('background-color:#ebe6da;padding-top:90px;padding-bottom:70px', false)
            ->assertSee("--fuente-titulo: 'Lora'", false)->assertSee('font-size: 18px', false);
    }

    public function test_cloudflare_failure_keeps_the_local_photograph_available(): void
    {
        Storage::fake('local');
        config(['services.cloudflare.account_id' => 'test-account', 'services.cloudflare.token' => 'private-test-token']);
        Http::preventStrayRequests();
        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => false], 503)]);
        $this->actingAs($this->owner());
        $image = UploadedFile::fake()->createWithContent('boda.webp', file_get_contents(public_path('assets/images/hero/hero-01.webp')));
        $this->post('/admin/media', ['image' => $image])->assertSessionHasErrors('image');
        $asset = MediaAsset::firstOrFail();
        $this->assertSame('local', $asset->provider);
        $this->get('/media/'.$asset->id)->assertOk();
    }
}
