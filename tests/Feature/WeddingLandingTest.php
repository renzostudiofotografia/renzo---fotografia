<?php

namespace Tests\Feature;

use App\LandingContent;
use App\Models\LandingPage;
use App\Models\SiteSetting;
use App\Models\User;
use App\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WeddingLandingTest extends TestCase
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

    /** @param array<string, mixed> $data */
    private function landing(array $data, string $slug = 'bodas-de-prueba', bool $published = false): LandingPage
    {
        return LandingPage::create([
            'name' => 'Bodas de prueba', 'slug' => $slug,
            'draft' => $data, 'published' => $published ? $data : null,
            'revision' => 1, 'published_at' => $published ? now() : null,
        ]);
    }

    /** @return array<string, mixed> */
    private function story(string $names, string $image, bool $enabled = true): array
    {
        return [
            'names' => $names, 'title' => 'Historia editorial de prueba', 'location' => 'Arequipa',
            'date' => '2026', 'image' => $image, 'alt' => 'Portada de prueba',
            'description' => 'Una historia creada exclusivamente para las pruebas.', 'enabled' => $enabled,
            'media_type' => 'image', 'video_url' => '', 'video_poster' => '',
            'images' => [
                ['image' => 'assets/images/details/rings.webp', 'alt' => 'Segundo instante del relato', 'media_type' => 'image', 'video_url' => '', 'video_poster' => ''],
                ['image' => 'assets/images/details/flowers.webp', 'alt' => 'Primer instante del relato', 'media_type' => 'image', 'video_url' => '', 'video_poster' => ''],
            ],
        ];
    }

    private function document(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        return new \DOMXPath($document);
    }

    public function test_wedding_preset_reuses_existing_assets_without_promoting_unverified_claims_or_a_false_portrait(): void
    {
        $data = app(LandingContent::class)->defaults('wedding');

        $this->assertSame('wedding', $data['visual_theme']);
        $this->assertSame([], $data['trust_metrics']);
        $this->assertSame([], $data['testimonials']);
        $this->assertSame('', collect($data['sections'])->firstWhere('type', 'about')['image']);
        $this->assertNotSame('51900000000', $data['whatsapp']);
        $this->assertNotSame('https://instagram.com', $data['instagram_url']);
        $this->assertFalse($data['calculator_enabled']);
        $this->assertEquals(0, $data['price']);

        $knownImages = collect(config('site.portfolio'))->pluck('image')->all();
        $this->assertNotEmpty($data['stories']);
        foreach ($data['stories'] as $story) {
            $this->assertContains($story['image'], $knownImages);
            $this->assertSame([], $story['images']);
        }
        $knownServices = collect(config('site.services'))->pluck('title')->all();
        $this->assertNotEmpty($data['experiences']);
        foreach ($data['experiences'] as $experience) {
            $this->assertContains($experience['name'], $knownServices);
            $this->assertEquals(0, $experience['price']);
            $this->assertFalse($experience['show_price']);
        }

        $this->actingAs($this->owner())->post('/admin/landings', [
            'name' => 'Bodas boutique', 'slug' => 'bodas-boutique', 'preset' => 'wedding',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing = LandingPage::where('slug', 'bodas-boutique')->firstOrFail();
        $this->assertSame('wedding', $landing->draft['visual_theme']);
        $this->assertNull($landing->published);
        $this->get('/lp/bodas-boutique')->assertNotFound();
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
            File::put(storage_path('app/visual-review/wedding-preset.html'), $preview->getContent());
        }
        $editor = $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk()
            ->assertSee('Historias reales de bodas')->assertSee('Experiencias de boda');
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            file_put_contents(storage_path('app/visual-review/wedding-editor.html'), $editor->getContent());
        }
    }

    public function test_budget_estimator_requires_enabled_real_rates_and_preserves_administrative_pricing_controls(): void
    {
        $data = app(LandingContent::class)->defaults('wedding');
        $data['calculator_enabled'] = true;
        $landing = $this->landing($data, published: true);
        $this->get('/lp/'.$landing->slug)->assertOk()->assertDontSee('data-wedding-calculator', false);

        $data['calculator_rates'] = [
            ['label' => 'Cobertura de prueba', 'category' => 'coverage', 'amount' => 4800.50, 'enabled' => true],
            ['label' => 'Arequipa de prueba', 'category' => 'city', 'amount' => 0, 'enabled' => true],
            ['label' => 'Álbum de prueba', 'category' => 'extra', 'amount' => 650, 'enabled' => true],
            ['label' => 'Tarifa oculta de prueba', 'category' => 'coverage', 'amount' => 9000, 'enabled' => false],
        ];
        $data['calculator_title'] = 'Estimado administrado de prueba';
        $data['price'] = 12345.67;
        $data['price_visible'] = false;
        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->fresh()->revision])
            ->assertRedirect()->assertSessionHasNoErrors();
        $response = $this->get('/lp/'.$landing->slug)->assertOk();
        $response->assertSee('data-wedding-calculator', false)->assertSee('Estimado administrado de prueba')
            ->assertSee('data-amount="4800.5"', false)->assertSee('Álbum de prueba')
            ->assertDontSee('Tarifa oculta de prueba')->assertDontSee('12,345.67');

        $data['calculator_rates'] = [['label' => 'Cobertura sin tarifa confirmada', 'category' => 'coverage', 'amount' => 0, 'enabled' => true]];
        $landing->update(['published' => $data]);
        $this->get('/lp/'.$landing->slug)->assertOk()->assertDontSee('data-wedding-calculator', false);
        $data['calculator_rates'][0]['amount'] = 4800.50;
        $data['calculator_enabled'] = false;
        $landing->update(['published' => $data]);
        $this->get('/lp/'.$landing->slug)->assertOk()->assertDontSee('data-wedding-calculator', false);
    }

    public function test_wedding_preset_imports_explicitly_published_business_information_and_keeps_main_site_drafts_private(): void
    {
        SiteSetting::create([
            'section' => 'home_experience', 'revision' => 1,
            'published' => ['weddings' => '+220', 'years' => '12', 'field_1' => 'Bodas documentadas', 'field_2' => 'Años de trayectoria'],
            'draft' => ['weddings' => '+999', 'years' => '80'],
        ]);
        SiteSetting::create([
            'section' => 'testimonials', 'revision' => 1,
            'published' => ['items' => [['names' => 'Pareja publicada de prueba', 'quote' => 'Opinión aprobada de prueba.', 'detail' => 'Arequipa']]],
            'draft' => ['items' => [['names' => 'Pareja privada de prueba', 'quote' => 'Opinión privada.', 'detail' => 'Privado']]],
        ]);
        SiteSetting::create([
            'section' => 'portfolio_items', 'revision' => 1,
            'published' => ['items' => [['category' => 'wedding', 'tag' => 'Boda de prueba', 'names' => 'Historia publicada de prueba', 'location' => 'Arequipa', 'image' => 'assets/images/hero/hero-02.webp']]],
            'draft' => ['items' => [['names' => 'Historia privada de prueba', 'image' => 'assets/images/hero/hero-03.webp']]],
        ]);
        app(SiteContent::class)->forget();

        $data = app(LandingContent::class)->defaults('wedding');
        $this->assertContains('+220', array_column($data['trust_metrics'], 'value'));
        $this->assertContains('12', array_column($data['trust_metrics'], 'value'));
        $this->assertSame('Pareja publicada de prueba', $data['testimonials'][0]['name']);
        $this->assertSame('Historia publicada de prueba', $data['stories'][0]['names']);
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('Pareja privada', $encoded);
        $this->assertStringNotContainsString('Historia privada', $encoded);
        $this->assertStringNotContainsString('+999', $encoded);
        $this->assertSame([], $data['stories'][0]['images']);
    }

    public function test_applying_wedding_design_preserves_existing_content_media_identity_and_the_published_version(): void
    {
        $original = array_replace(config('landing.defaults'), [
            'page_title' => 'Título SEO conservado', 'pixel_id' => '12345678901', 'price' => 8450,
            'whatsapp' => '51987654321', 'hero_media' => 'video',
            'hero_video_url' => 'https://example.test/pelicula.mp4',
            'hero_video_poster' => 'assets/images/hero/hero-04.webp',
            'gallery' => [['image' => 'assets/images/hero/hero-03.webp', 'alt' => 'Mi fotografía elegida', 'media_type' => 'image', 'video_url' => '', 'video_poster' => '']],
            'stories' => [$this->story('Nuestra historia conservada', 'assets/images/portfolio/sofia-gabriel.webp')],
            'trust_metrics' => [['value' => 'Nuestro dato real', 'label' => 'Nuestra trayectoria', 'enabled' => true]],
            'process_steps' => [['title' => 'Nuestro proceso conservado', 'text' => 'Descripción propia.', 'enabled' => true]],
            'testimonials' => [[
                'name' => 'Pareja conservada de prueba', 'quote' => 'Opinión aprobada que se debe conservar.',
                'image' => 'assets/images/portfolio/camila-sebastian.webp', 'role' => 'Boda de prueba',
                'location' => 'Arequipa', 'date' => '2026', 'rating' => 5,
                'review_url' => 'https://example.test/resena-publicada', 'enabled' => true,
                'media_type' => 'image', 'video_url' => '', 'video_poster' => '',
            ]],
        ]);
        $original['sections'][0]['title'] = 'Nuestra portada conservada';
        $original['sections'][0]['button_url'] = 'https://wa.me/51987654321';
        $landing = $this->landing($original, published: true);
        $publishedAt = $landing->published_at->toISOString();

        $this->actingAs($this->owner())->post('/admin/landings/'.$landing->id.'/wedding', ['revision' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();

        $this->assertSame('bodas-de-prueba', $landing->slug);
        $this->assertSame('Bodas de prueba', $landing->name);
        $this->assertSame(2, $landing->revision);
        $this->assertSame($original, $landing->published);
        $this->assertSame($publishedAt, $landing->published_at->toISOString());
        $this->assertSame('wedding', $landing->draft['visual_theme']);
        foreach (['page_title', 'pixel_id', 'price', 'whatsapp', 'hero_media', 'hero_video_url', 'hero_video_poster', 'gallery', 'stories', 'trust_metrics', 'process_steps', 'testimonials'] as $key) {
            $this->assertSame($original[$key], $landing->draft[$key]);
        }
        $hero = collect($landing->draft['sections'])->firstWhere('type', 'hero');
        $this->assertSame('Nuestra portada conservada', $hero['title']);
        $this->assertSame('https://wa.me/51987654321', $hero['button_url']);
        foreach (['trust', 'stories', 'about', 'experiences', 'process', 'closing'] as $type) {
            $this->assertTrue(collect($landing->draft['sections'])->contains('type', $type));
        }
        $this->get('/lp/'.$landing->slug)->assertOk()->assertSee('Nuestra portada conservada');

        $this->post('/admin/landings/'.$landing->id.'/wedding', ['revision' => 1])->assertSessionHasErrors('revision');
        $this->assertSame(2, $landing->fresh()->revision);
    }

    public function test_wedding_design_action_requires_the_existing_administrative_permission(): void
    {
        $landing = $this->landing(config('landing.defaults'));
        $this->post('/admin/landings/'.$landing->id.'/wedding', ['revision' => 1])->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())
            ->post('/admin/landings/'.$landing->id.'/wedding', ['revision' => 1])->assertForbidden();
        $this->assertSame(1, $landing->fresh()->revision);
        $this->assertSame('classic', $landing->fresh()->draft['visual_theme']);
    }

    public function test_nested_stories_and_modular_content_remain_editable_ordered_and_private_until_publication(): void
    {
        $data = app(LandingContent::class)->defaults('wedding');
        $landing = $this->landing($data);
        $first = $this->story('Primera pareja de prueba', 'assets/images/portfolio/camila-sebastian.webp');
        $second = $this->story('Segunda pareja de prueba', 'assets/images/portfolio/mariana-diego.webp');
        $hidden = $this->story('Pareja oculta de prueba', 'assets/images/portfolio/lucia-mateo.webp', false);
        $data['stories'] = [$second, $first, $hidden];
        $data['trust_metrics'] = [
            ['value' => 'Dato verificado de prueba', 'label' => 'Trayectoria de prueba', 'enabled' => true],
            ['value' => 'Dato oculto de prueba', 'label' => 'No publicar', 'enabled' => false],
        ];
        $data['process_steps'] = [
            ['title' => 'Conversación de prueba', 'text' => 'Contenido administrado.', 'enabled' => true],
            ['title' => 'Paso oculto de prueba', 'text' => 'No publicar.', 'enabled' => false],
        ];
        $data['experiences'] = [[
            'name' => 'Cobertura publicada de prueba', 'subtitle' => 'Colección de prueba',
            'description' => 'Experiencia creada únicamente para esta prueba.',
            'image' => 'assets/images/services/wedding-documentary.webp', 'alt' => 'Cobertura de prueba',
            'inclusions' => "Preparación de prueba\nGalería de prueba", 'hours' => 8,
            'price' => 7250, 'show_price' => false, 'button_label' => 'Consultar esta experiencia',
            'button_url' => '#contacto', 'featured' => true, 'enabled' => true,
        ]];
        $data['hero_mobile_image'] = 'assets/images/hero/hero-04.webp';
        $data['hero_secondary_label'] = 'Nuestras historias editadas';
        $data['hero_secondary_url'] = '#historias';
        $data['about_bio'] = 'Biografía aprobada de prueba.';
        $data['contact_email'] = 'estudio@example.test';
        $data['ga4_id'] = 'G-TEST123456';
        $sections = collect($data['sections']);
        $storiesSection = $sections->firstWhere('type', 'stories');
        $storiesSection['title'] = 'Historias administradas de prueba';
        $data['sections'] = [
            $sections->firstWhere('type', 'hero'), $storiesSection,
            ...$sections->reject(fn (array $section): bool => in_array($section['type'], ['hero', 'stories'], true))->all(),
        ];

        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame(['Segunda pareja de prueba', 'Primera pareja de prueba', 'Pareja oculta de prueba'], array_column($landing->draft['stories'], 'names'));
        $this->assertSame($second['images'], $landing->draft['stories'][0]['images']);
        $this->assertSame('stories', $landing->draft['sections'][1]['type']);
        $this->assertNull($landing->published);
        $this->get('/lp/'.$landing->slug)->assertNotFound();
        $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk()
            ->assertSee('Historias administradas de prueba')->assertSee('Segunda pareja de prueba');

        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
            ->assertRedirect()->assertSessionHasNoErrors();
        $public = $this->get('/lp/'.$landing->slug)->assertOk();
        $public->assertSeeInOrder(['Segunda pareja de prueba', 'Primera pareja de prueba'])
            ->assertSee('Cobertura publicada de prueba')->assertSee('Dato verificado de prueba')
            ->assertSee('Conversación de prueba')->assertDontSee('Pareja oculta de prueba')
            ->assertDontSee('Dato oculto de prueba')->assertDontSee('Paso oculto de prueba')
            ->assertDontSee('7,250.00')->assertDontSee('7250');
        $this->assertSame($landing->fresh()->draft, $landing->fresh()->published);
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            file_put_contents(storage_path('app/visual-review/wedding-landing.html'), $public->getContent());
        }
    }

    public function test_story_galleries_are_deferred_and_the_mobile_cover_and_seo_are_rendered_from_saved_settings(): void
    {
        $data = app(LandingContent::class)->defaults('wedding');
        $data['stories'] = [$this->story('Galería inmersiva de prueba', 'assets/images/portfolio/camila-sebastian.webp')];
        $data['hero_mobile_image'] = 'assets/images/hero/hero-04.webp';
        $data['page_title'] = 'Fotógrafo de bodas Arequipa · prueba';
        $data['meta_description'] = 'Descripción SEO administrada para bodas de prueba.';
        $landing = $this->landing($data, published: true);

        $response = $this->get('/lp/'.$landing->slug)->assertOk()
            ->assertSee('Fotógrafo de bodas Arequipa · prueba')->assertSee('Descripción SEO administrada para bodas de prueba.');
        $xpath = $this->document($response->getContent());
        $this->assertGreaterThan(0, $xpath->query('//picture/source[contains(@srcset, "hero-04.webp")]')->length);
        $this->assertCount(1, $xpath->query('//button[@data-story-open]'));
        $this->assertCount(1, $xpath->query('//dialog[@data-story-dialog]'));
        $this->assertCount(3, $xpath->query('//template[@data-story-template]//img[@data-src and not(@src)]'));
        $this->assertCount(2, $xpath->query('//template[@data-story-template]//img[contains(@data-src, "details/rings.webp") or contains(@data-src, "details/flowers.webp")]'));
        $this->assertCount(0, $xpath->query('//template[@data-story-template]//img[@src]'));
        $this->assertCount(1, $xpath->query('//link[@rel="canonical"]'));
        $this->assertGreaterThan(0, $xpath->query('//script[@type="application/ld+json"]')->length);
    }

    public function test_new_configuration_rejects_unsafe_nested_links_and_invalid_email_or_measurement_ids_without_mutation(): void
    {
        $landing = $this->landing(app(LandingContent::class)->defaults('wedding'));
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());

        foreach ([
            ['ga4_id', 'G-TEST"><script>alert(1)</script>'],
            ['notification_email', "estudio@example.test\r\nBcc:otro@example.test"],
            ['contact_email', 'no-es-un-correo'],
            ['privacy_url', 'javascript:alert(1)'],
            ['hero_secondary_url', '//example.test/externo'],
            ['nav_links', [['label' => 'Enlace de prueba', 'url' => 'data:text/html,malicioso', 'enabled' => true]]],
            ['stories', [array_replace($this->story('Historia inválida', 'assets/images/hero/hero-01.webp'), ['images' => [['image' => 'https://example.test/privado.jpg', 'alt' => 'Recurso no permitido']]])]],
        ] as [$field, $invalid]) {
            $response = $this->putJson('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => 1,
                'data' => array_replace($landing->draft, [$field => $invalid]),
            ])->assertUnprocessable();
            $this->assertNotEmpty($response->json('errors'));
        }

        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }

    public function test_campaign_slug_can_be_edited_safely_and_remains_optional_for_older_editor_submissions(): void
    {
        $landing = $this->landing(config('landing.defaults'));
        $this->landing(config('landing.defaults'), 'slug-ocupado');
        $this->actingAs($this->owner());

        foreach (['slug-ocupado', '../admin', 'https://example.test/bodas'] as $slug) {
            $this->put('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'slug' => $slug, 'revision' => $landing->revision, 'data' => $landing->draft,
            ])->assertSessionHasErrors('slug');
        }
        $this->assertSame('bodas-de-prueba', $landing->fresh()->slug);
        $this->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'slug' => 'bodas-editoriales', 'revision' => $landing->revision, 'data' => $landing->draft,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame('bodas-editoriales', $landing->slug);

        $this->put('/admin/landings/'.$landing->id, [
            'name' => 'Nombre actualizado', 'revision' => $landing->revision, 'data' => $landing->draft,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('bodas-editoriales', $landing->fresh()->slug);
    }
}
