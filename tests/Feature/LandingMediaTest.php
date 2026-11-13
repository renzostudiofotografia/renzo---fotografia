<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LandingMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('LANDING_CAPTURE')) {
            URL::forceRootUrl('http://127.0.0.1:8010');
        }
    }

    private function capture(string $name, string $html): void
    {
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            file_put_contents(storage_path('app/visual-review/'.$name), $html);
        }
    }

    private function owner(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** @param array<string, mixed> $overrides */
    private function landing(bool $published = false, array $overrides = []): LandingPage
    {
        $data = array_replace(config('landing.defaults'), $overrides);

        return LandingPage::create([
            'name' => 'Campaña multimedia de prueba', 'slug' => 'campana-multimedia',
            'draft' => $data, 'published' => $published ? $data : null,
            'revision' => 1, 'published_at' => $published ? now() : null,
        ]);
    }

    private function document(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        return new \DOMXPath($document);
    }

    public function test_selected_carousel_images_and_testimonial_portraits_survive_save_and_publication(): void
    {
        $landing = $this->landing();
        $data = $landing->draft;
        $data = array_replace($data, [
            'hero_media' => 'carousel', 'gallery_layout' => 'carousel',
            'sticky_header' => true, 'sticky_cta' => true, 'button_animation' => 'shine',
            'light_effect' => 'aurora', 'image_motion' => 'pan',
            'carousel_autoplay' => true, 'carousel_interval' => 7,
            'hero_images' => [
                ['image' => 'assets/images/hero/hero-02.webp', 'alt' => 'Primera fotografía elegida de prueba'],
                ['image' => 'assets/images/hero/hero-01.webp', 'alt' => 'Segunda fotografía elegida de prueba'],
            ],
            'testimonials' => [[
                'name' => 'Cliente de prueba', 'quote' => 'Opinión utilizada únicamente para verificar la vista.',
                'image' => 'assets/images/portfolio/sofia-gabriel.webp', 'role' => 'Sesión de prueba',
            ]],
        ]);
        if (! collect($data['sections'])->contains('type', 'testimonials')) {
            $data['sections'][] = array_replace($data['sections'][0], [
                'type' => 'testimonials', 'title' => 'Opiniones de prueba', 'image' => '',
            ]);
        }
        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame($data['hero_images'], $landing->draft['hero_images']);
        $this->assertSame($data['testimonials'], $landing->draft['testimonials']);
        $this->assertSame(7, $landing->draft['carousel_interval']);
        $this->assertSame('shine', $landing->draft['button_animation']);
        $this->assertSame('aurora', $landing->draft['light_effect']);
        $this->assertSame('pan', $landing->draft['image_motion']);

        $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
            ->assertRedirect()->assertSessionHasNoErrors();
        $public = $this->get('/lp/'.$landing->slug)->assertOk();
        $this->capture('landing-multimedia.html', $public->getContent());
        if (getenv('LANDING_CAPTURE')) {
            $editor = $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk();
            $this->capture('landing-multimedia-editor.html', $editor->getContent());
        }
        foreach ([$preview, $public] as $response) {
            $xpath = $this->document($response->getContent());
            $images = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-carousel ")]//*[@data-carousel-slide]//img');
            $this->assertCount(2, $images);
            $this->assertSame('Primera fotografía elegida de prueba', $images->item(0)->getAttribute('alt'));
            $this->assertSame('Segunda fotografía elegida de prueba', $images->item(1)->getAttribute('alt'));
            $this->assertCount(1, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " gallery-carousel ")][@data-carousel]'));
            $this->assertCount(1, $xpath->query('//img[contains(concat(" ", normalize-space(@class), " "), " testimonial-photo ")][@alt="Cliente de prueba"]'));
            $response->assertSee('Sesión de prueba')->assertSee('Opinión utilizada únicamente para verificar la vista.');
        }
        $preview->assertDontSee('connect.facebook.net', false)->assertDontSee('fbevents.js', false);
    }

    public function test_video_poster_and_the_facebook_thumbnail_are_independently_editable(): void
    {
        $landing = $this->landing(published: true, overrides: [
            'hero_media' => 'video', 'hero_video_url' => 'https://media.example.test/presentacion.mp4',
            'hero_video_poster' => 'assets/images/hero/hero-02.webp',
            'share_image' => 'assets/images/portfolio/valentina-alejandro.webp',
            'hero_video_autoplay' => false,
        ]);
        $response = $this->get('/lp/'.$landing->slug)->assertOk();
        $this->capture('landing-video.html', $response->getContent());
        $xpath = $this->document($response->getContent());
        $videos = $xpath->query('//video');
        $this->assertCount(1, $videos);
        $video = $videos->item(0);
        $this->assertSame('https://media.example.test/presentacion.mp4', $video->getAttribute('src'));
        $this->assertSame(asset('assets/images/hero/hero-02.webp'), $video->getAttribute('poster'));
        $this->assertTrue($video->hasAttribute('controls'));
        $this->assertTrue($video->hasAttribute('playsinline'));
        $this->assertFalse($video->hasAttribute('autoplay'));
        $this->assertSame(asset('assets/images/portfolio/valentina-alejandro.webp'), $xpath->query('//meta[@property="og:image"]')->item(0)->getAttribute('content'));

        $this->actingAs($this->owner());
        $data = array_replace($landing->draft, [
            'hero_video_poster' => 'assets/images/hero/hero-03.webp', 'share_image' => 'assets/images/hero/hero-04.webp',
        ]);
        $this->put('/admin/landings/'.$landing->id, ['name' => $landing->name, 'revision' => 1, 'data' => $data])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/lp/'.$landing->slug)->assertSee(asset('assets/images/hero/hero-02.webp'), false);
        $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
        $previewXpath = $this->document($preview->getContent());
        $this->assertSame(asset('assets/images/hero/hero-03.webp'), $previewXpath->query('//video')->item(0)->getAttribute('poster'));
        $this->assertSame(asset('assets/images/hero/hero-04.webp'), $previewXpath->query('//meta[@property="og:image"]')->item(0)->getAttribute('content'));
    }

    public function test_flyer_mode_uses_the_selected_cover_image_without_a_video_or_carousel(): void
    {
        $landing = $this->landing();
        $data = array_replace($landing->draft, ['hero_media' => 'flyer', 'hero_layout' => 'full', 'hero_image_fit' => 'cover']);
        $data['sections'][0]['image'] = 'assets/images/hero/hero-03.webp';
        $data['sections'][0]['alt'] = 'Flyer de la campaña de prueba';
        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $response = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
        $this->capture('landing-flyer.html', $response->getContent());
        $xpath = $this->document($response->getContent());
        $flyer = $xpath->query('//figure[contains(concat(" ", normalize-space(@class), " "), " is-flyer ")]//img');
        $this->assertCount(1, $flyer);
        $this->assertSame(asset('assets/images/hero/hero-03.webp'), $flyer->item(0)->getAttribute('src'));
        $this->assertSame('Flyer de la campaña de prueba', $flyer->item(0)->getAttribute('alt'));
        $this->assertCount(0, $xpath->query('//video'));
        $this->assertCount(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-carousel ")]'));
    }

    public function test_older_campaigns_and_text_only_testimonials_render_without_rewriting_their_saved_content(): void
    {
        $landing = $this->landing(published: true);
        $legacyData = $landing->draft;
        foreach ([
            'sticky_header', 'button_animation', 'light_effect', 'light_color', 'light_intensity', 'image_motion', 'hero_media',
            'hero_image_fit', 'hero_focal_point', 'hero_video_url', 'hero_video_poster', 'hero_video_autoplay',
            'hero_images', 'carousel_autoplay', 'carousel_interval', 'gallery_layout', 'share_image', 'trust_points',
        ] as $field) {
            unset($legacyData[$field]);
        }
        $legacyData['testimonials'] = [['name' => 'Cliente antiguo de prueba', 'quote' => 'Opinión antigua de prueba sin fotografía.']];
        if (! collect($legacyData['sections'])->contains('type', 'testimonials')) {
            $legacyData['sections'][] = array_replace($legacyData['sections'][0], ['type' => 'testimonials', 'title' => 'Opiniones', 'image' => '']);
        }
        $landing->update(['draft' => $legacyData, 'published' => $legacyData]);
        $landing->refresh();
        $before = $landing->getRawOriginal();

        $this->get('/lp/'.$landing->slug)->assertOk()->assertSee('Opinión antigua de prueba sin fotografía.');
        $this->actingAs($this->owner());
        $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk()->assertSee('Cliente antiguo de prueba');
        $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk()
            ->assertViewHas('data', fn (array $data): bool => array_key_exists('hero_media', $data)
                && $data['testimonials'][0]['image'] === '' && $data['testimonials'][0]['role'] === '');
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }

    public function test_media_configuration_rejects_unsafe_sources_and_invalid_motion_settings(): void
    {
        $landing = $this->landing();
        $this->actingAs($this->owner());
        foreach ([
            ['hero_video_url' => 'javascript:alert(1)'],
            ['hero_video_url' => 'data:video/mp4;base64,AAAA'],
            ['hero_video_url' => 'http://media.example.test/presentacion.mp4'],
            ['hero_video_poster' => 'javascript:alert(1)'],
            ['share_image' => 'https://untrusted.example.test/tracker.svg'],
            ['hero_media' => 'untrusted-embed'],
            ['button_animation' => 'untrusted-effect'],
            ['light_effect' => 'url(javascript:alert(1))'],
            ['light_color' => '#fff; background:url(https://example.test)'],
            ['light_intensity' => 101],
            ['carousel_interval' => 2],
            ['carousel_interval' => 13],
            ['hero_images' => array_fill(0, 11, ['image' => 'assets/images/hero/hero-01.webp', 'alt' => 'Prueba'])],
            ['testimonials' => [['name' => 'Prueba', 'quote' => 'Prueba', 'image' => 'javascript:alert(1)', 'role' => 'Prueba']]],
        ] as $invalid) {
            $this->putJson('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => 1, 'data' => array_replace($landing->draft, $invalid),
            ])->assertUnprocessable();
        }
        $this->assertSame($landing->draft, $landing->fresh()->draft);
        $this->assertSame(1, $landing->fresh()->revision);
    }

    public function test_removing_all_carousel_images_and_trust_points_keeps_them_empty_after_saving(): void
    {
        $landing = $this->landing();
        $data = $landing->draft;
        $data['hero_media'] = 'carousel';
        unset($data['hero_images'], $data['trust_points']);
        $this->actingAs($this->owner())->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame([], $landing->draft['hero_images']);
        $this->assertSame([], $landing->draft['trust_points']);
        $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
        $xpath = $this->document($preview->getContent());
        $images = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-carousel ")]//*[@data-carousel-slide]//img');
        $this->assertCount(1, $images);
        $this->assertSame(asset($data['sections'][0]['image']), $images->item(0)->getAttribute('src'));
        $this->assertCount(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-trust ")]'));
        $this->assertSame([], $landing->fresh()->draft['hero_images']);
    }

    public function test_video_upload_requires_an_owner_and_rejects_disguised_or_oversized_files(): void
    {
        Storage::fake('local');
        config(['services.cloudflare_r2.account_id' => null]);
        $landing = $this->landing();
        $endpoint = '/admin/landings/'.$landing->id.'/video';
        $this->post($endpoint, [])->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->postJson($endpoint, [])->assertForbidden();
        $this->actingAs($this->owner());
        foreach ([
            UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
            UploadedFile::fake()->create('falso.mp4', 10, 'text/html'),
            UploadedFile::fake()->create('video.html', 10, 'video/mp4'),
            UploadedFile::fake()->create('demasiado-grande.mp4', 51201, 'video/mp4'),
        ] as $file) {
            $this->postJson($endpoint, ['video' => $file])->assertUnprocessable()->assertJsonValidationErrors('video');
        }
        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_an_uploaded_video_is_served_with_its_mime_and_never_appears_as_an_image_option(): void
    {
        Storage::fake('local');
        config(['services.cloudflare_r2.account_id' => null]);
        Http::preventStrayRequests();
        $landing = $this->landing();
        $before = $landing->draft;
        $this->actingAs($this->owner());
        $upload = $this->postJson('/admin/landings/'.$landing->id.'/video', [
            'video' => UploadedFile::fake()->create('presentacion.mp4', 1024, 'video/mp4'),
        ])->assertCreated()->assertJsonStructure(['id', 'url', 'name']);
        $asset = MediaAsset::findOrFail($upload->json('id'));
        $this->assertSame('/media/'.$asset->id, $upload->json('url'));
        $this->assertSame('video/mp4', $asset->mime_type);
        $this->assertSame('local', $asset->provider);
        Storage::disk('local')->assertExists($asset->path);
        $this->get('/media/'.$asset->id)->assertOk()->assertHeader('Content-Type', 'video/mp4');
        $this->assertSame($before, $landing->fresh()->draft);

        foreach (['/admin/landings/'.$landing->id.'/edit', '/admin/editor/home_hero'] as $editor) {
            $response = $this->get($editor)->assertOk();
            $xpath = $this->document($response->getContent());
            $this->assertCount(0, $xpath->query('//*[@id="media-picker"]//*[@data-image="/media/'.$asset->id.'"]'));
        }
        $media = $this->get('/admin/media')->assertOk();
        $this->assertCount(1, $this->document($media->getContent())->query('//video'));
        $this->postJson('/admin/media/'.$asset->id.'/cloudflare')->assertUnprocessable()->assertJsonValidationErrors('image');
        Http::assertNothingSent();
    }

    public function test_r2_video_delivery_bypasses_the_image_optimizer(): void
    {
        config(['services.cloudflare_r2.public_url' => 'https://media.example.test', 'services.cloudflare_r2.optimize' => true]);
        Http::preventStrayRequests();
        $asset = MediaAsset::create([
            'name' => 'Video de prueba', 'alt' => '', 'provider' => 'r2', 'path' => 'site-media/presentacion.mp4',
            'mime_type' => 'video/mp4', 'size' => 1024, 'cloudflare_id' => 'site-media/presentacion.mp4',
            'cloudflare_url' => 'https://media.example.test/site-media/presentacion.mp4',
        ]);
        $this->get('/media/'.$asset->id)->assertRedirect('https://media.example.test/site-media/presentacion.mp4');
        Http::assertNothingSent();
    }
}
