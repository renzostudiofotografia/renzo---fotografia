<?php

namespace Tests\Feature;

use App\LandingContent;
use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingMediaExperienceTest extends TestCase
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
    private function landing(array $data, bool $published = false): LandingPage
    {
        return LandingPage::create([
            'name' => 'Experiencia multimedia de prueba', 'slug' => 'experiencia-multimedia',
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

    private function captureBrowserDatabase(LandingPage $landing, User $owner): void
    {
        $directory = storage_path('app/visual-review');
        File::ensureDirectoryExists($directory);
        $prefix = $directory.'/landing-browser-qa-'.Str::uuid();
        $source = DB::connection()->getPdo();
        $target = new \PDO('sqlite:'.$prefix.'.sqlite');
        $target->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $target->exec('PRAGMA foreign_keys = OFF');
        $identifier = static fn (string $name): string => '"'.str_replace('"', '""', $name).'"';
        $tables = $source->query("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_ASSOC);
        $target->beginTransaction();
        try {
            foreach ($tables as $table) {
                $target->exec($table['sql']);
                $rows = $source->query('SELECT * FROM '.$identifier($table['name']))->fetchAll(\PDO::FETCH_ASSOC);
                if ($rows === []) {
                    continue;
                }
                $columns = array_map($identifier, array_keys($rows[0]));
                $statement = $target->prepare('INSERT INTO '.$identifier($table['name']).' ('.implode(',', $columns).') VALUES ('.implode(',', array_fill(0, count($columns), '?')).')');
                foreach ($rows as $row) {
                    $statement->execute(array_values($row));
                }
            }
            $indexes = $source->query("SELECT sql FROM sqlite_master WHERE type = 'index' AND sql IS NOT NULL")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($indexes as $sql) {
                $target->exec($sql);
            }
            $target->commit();
        } catch (\Throwable $exception) {
            $target->rollBack();
            throw $exception;
        }
        File::put($prefix.'.json', json_encode([
            'purpose' => 'Isolated browser QA fixture copied from the in-memory test database.',
            'database' => $prefix.'.sqlite', 'landing_id' => $landing->id, 'slug' => $landing->slug,
            'email' => $owner->email, 'password' => 'password',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /** @return array<string, mixed> */
    private function mediaContent(): array
    {
        $data = app(LandingContent::class)->defaults('wedding');
        $data['button_icon'] = 'calendar-days';
        $data['text_alignment'] = 'center';
        $data['sticky_header'] = true;
        $data['sticky_cta'] = true;
        $data['stories'] = [[
            'names' => 'Historia audiovisual de prueba', 'title' => 'Un relato administrable',
            'location' => 'Arequipa', 'date' => '', 'image' => '', 'alt' => 'Portada del relato',
            'description' => 'Contenido exclusivo de una prueba de integración.', 'enabled' => true,
            'media_type' => 'video', 'video_url' => 'https://example.test/historia-portada.mp4',
            'video_poster' => 'assets/images/portfolio/camila-sebastian.webp',
            'images' => [
                ['image' => 'assets/images/details/rings.webp', 'alt' => 'Fotografía dentro del relato', 'media_type' => 'image', 'video_url' => '', 'video_poster' => ''],
                ['image' => '', 'alt' => 'Video dentro del relato', 'media_type' => 'video', 'video_url' => 'https://example.test/historia-detalle.webm', 'video_poster' => 'assets/images/details/flowers.webp'],
            ],
        ]];
        $data['testimonials'] = [[
            'name' => 'Pareja de prueba', 'quote' => 'Opinión autorizada de prueba con video.',
            'image' => 'assets/images/portfolio/sofia-gabriel.webp', 'role' => 'Celebración de prueba',
            'location' => 'Arequipa', 'date' => '', 'rating' => 0, 'review_url' => '', 'enabled' => true,
            'media_type' => 'video', 'video_url' => 'https://example.test/testimonio.mp4',
            'video_poster' => 'assets/images/portfolio/sofia-gabriel.webp',
        ]];
        $data['experiences'][0] = array_replace($data['experiences'][0], [
            'media_type' => 'video', 'video_url' => 'https://example.test/experiencia.mp4',
            'video_poster' => 'assets/images/services/wedding-documentary.webp',
        ]);
        $data['gallery'] = [
            ['image' => '', 'alt' => 'Video de la galería', 'media_type' => 'video', 'video_url' => 'https://example.test/galeria.mp4', 'video_poster' => 'assets/images/hero/hero-02.webp'],
        ];
        $data['sections'][] = array_replace($data['sections'][0], [
            'type' => 'gallery', 'title' => 'Galería multimedia de prueba', 'image' => '',
        ]);
        foreach ($data['sections'] as &$section) {
            if ($section['type'] === 'text') {
                $section = array_replace($section, [
                    'media_type' => 'video', 'video_url' => 'https://example.test/seccion.mp4',
                    'video_poster' => 'assets/images/hero/hero-03.webp', 'alignment' => 'right',
                ]);
            }
        }
        unset($section);

        return $data;
    }

    public function test_administrators_can_save_preview_and_publish_images_and_videos_with_minimal_controls(): void
    {
        $data = $this->mediaContent();
        $landing = $this->landing(app(LandingContent::class)->defaults('wedding'));
        $owner = $this->owner();
        $this->actingAs($owner)->put('/admin/landings/'.$landing->id, [
            'name' => $landing->name, 'revision' => 1, 'data' => $data,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $landing->refresh();
        $this->assertSame('calendar-days', $landing->draft['button_icon']);
        $this->assertSame('center', $landing->draft['text_alignment']);
        $this->assertFalse($landing->draft['sticky_header']);
        foreach (['stories', 'testimonials', 'experiences', 'gallery'] as $collection) {
            $this->assertSame($data[$collection][0]['video_url'], $landing->draft[$collection][0]['video_url']);
            $this->assertSame($data[$collection][0]['video_poster'], $landing->draft[$collection][0]['video_poster']);
            $this->assertSame('video', $landing->draft[$collection][0]['media_type']);
        }
        $this->assertSame($data['stories'][0]['images'][1]['video_url'], $landing->draft['stories'][0]['images'][1]['video_url']);

        $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
        $editor = $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk();
        foreach (['button_icon', 'text_alignment', 'stories][0][video_url', 'testimonials][0][video_url', 'gallery][0][video_url'] as $field) {
            $editor->assertSee('name="data['.$field.']"', false);
        }
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
            ->assertRedirect()->assertSessionHasNoErrors();
        $public = $this->get('/lp/'.$landing->slug)->assertOk();

        foreach ([$preview, $public] as $response) {
            $response->assertSee('Historia audiovisual de prueba')->assertSee('Opinión autorizada de prueba con video.')
                ->assertSee('testimonio.mp4', false)->assertSee('experiencia.mp4', false)->assertSee('galeria.mp4', false)
                ->assertSee('seccion.mp4', false)->assertDontSee('↗', false);
            $xpath = $this->document($response->getContent());
            $players = $xpath->query('//*[@data-media-player]');
            $this->assertGreaterThanOrEqual(5, $players->length);
            foreach ($players as $player) {
                $videos = $xpath->query('.//video[@data-landing-video]', $player);
                $this->assertCount(1, $videos);
                $video = $videos->item(0);
                $this->assertFalse($video->hasAttribute('controls'));
                $this->assertStringContainsString('nodownload', $video->getAttribute('controlslist'));
                $this->assertTrue($video->hasAttribute('disablepictureinpicture'));
                $this->assertCount(1, $xpath->query('.//button[@data-video-toggle]', $player));
                $this->assertCount(1, $xpath->query('.//button[@data-video-restart]', $player));
                $this->assertCount(2, $xpath->query('.//button', $player));
            }
            $this->assertCount(0, $xpath->query('//header[contains(concat(" ", normalize-space(@class), " "), " is-sticky ")]'));
            $this->assertCount(1, $xpath->query('//*[@data-floating-cta]'));
            $this->assertGreaterThan(0, $xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " landing-button ")]//svg')->length);
            $this->assertGreaterThan(0, $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " align-center ")]')->length);
            $this->assertGreaterThan(0, $xpath->query('//template[@data-story-template]//video')->length);
            $this->assertGreaterThan(0, $xpath->query('//template[@data-story-template]//video[@data-video-src]')->length);
            $this->assertCount(0, $xpath->query('//template[@data-story-template]//video[@src] | //template[@data-story-template]//source[@src]'));
            $this->assertGreaterThan(0, $xpath->query('//template[@data-story-template]//*[@data-src]')->length);
        }
        $this->assertSame($landing->fresh()->draft, $landing->fresh()->published);
        if (getenv('LANDING_CAPTURE')) {
            File::ensureDirectoryExists(storage_path('app/visual-review'));
            File::put(storage_path('app/visual-review/landing-media-experience.html'), $preview->getContent());
            File::put(storage_path('app/visual-review/landing-media-experience-editor.html'), $editor->getContent());
        }
        if (getenv('LANDING_BROWSER_QA')) {
            $this->captureBrowserDatabase($landing->fresh(), $owner);
        }
    }

    public function test_unsaved_media_alignment_and_icon_changes_render_immediately_without_mutating_saved_or_published_content(): void
    {
        $original = app(LandingContent::class)->defaults('wedding');
        $landing = $this->landing($original, published: true);
        $before = $landing->fresh()->getRawOriginal();
        $data = $this->mediaContent();
        $data['sections'][0]['title'] = 'Título cambiado sin guardar';
        $data['accent_color'] = '#9a7b45';
        $this->actingAs($this->owner());

        $first = $this->postJson('/admin/landings/'.$landing->id.'/preview', ['data' => $data])->assertOk();
        $first->assertSee('Título cambiado sin guardar')->assertSee('#9a7b45', false)
            ->assertSee('testimonio.mp4', false)->assertSee('"preview":true', false)
            ->assertSee('"pixelId":""', false)->assertSee('"ga4Id":""', false);
        $this->assertSame($before, $landing->fresh()->getRawOriginal());

        $data['sections'][0]['title'] = 'Segundo cambio todavía privado';
        $data['button_icon'] = 'none';
        $data['text_alignment'] = 'right';
        $data['testimonials'][0]['media_type'] = 'image';
        $second = $this->postJson('/admin/landings/'.$landing->id.'/preview', ['data' => $data])->assertOk();
        $second->assertSee('Segundo cambio todavía privado')->assertDontSee('Título cambiado sin guardar')
            ->assertDontSee('testimonio.mp4', false);
        $secondDocument = $this->document($second->getContent());
        $this->assertCount(0, $secondDocument->query('//a[contains(concat(" ", normalize-space(@class), " "), " landing-button ")]//svg'));
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
        $this->get('/lp/'.$landing->slug)->assertOk()->assertSee($original['sections'][0]['title'])
            ->assertDontSee('Segundo cambio todavía privado')->assertDontSee('testimonio.mp4', false);
    }

    public function test_older_photo_campaigns_receive_safe_media_defaults_without_rewriting_their_saved_json(): void
    {
        $data = app(LandingContent::class)->defaults('wedding');
        unset($data['button_icon'], $data['text_alignment']);
        $data['sticky_header'] = true;
        foreach ($data['sections'] as &$section) {
            unset($section['media_type'], $section['video_url'], $section['video_poster']);
        }
        unset($section);
        $data['sections'][0]['alignment'] = 'center';
        $data['stories'] = [[
            'names' => 'Historia antigua de prueba', 'title' => 'Portada fotográfica', 'location' => 'Arequipa',
            'date' => '', 'image' => 'assets/images/portfolio/camila-sebastian.webp', 'alt' => 'Fotografía antigua',
            'description' => '', 'enabled' => true,
            'images' => [['image' => 'assets/images/details/rings.webp', 'alt' => 'Detalle conservado']],
        ]];
        $data['testimonials'] = [['name' => 'Pareja antigua de prueba', 'quote' => 'Opinión conservada de prueba.', 'image' => 'assets/images/portfolio/sofia-gabriel.webp']];
        $data['gallery'] = [['image' => 'assets/images/hero/hero-02.webp', 'alt' => 'Fotografía conservada']];
        foreach ($data['experiences'] as &$experience) {
            unset($experience['media_type'], $experience['video_url'], $experience['video_poster']);
        }
        unset($experience);
        $landing = $this->landing($data, published: true);
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());

        foreach ([$this->get('/lp/'.$landing->slug), $this->get('/admin/landings/'.$landing->id.'/preview')] as $response) {
            $response->assertOk()->assertSee('Historia antigua de prueba')->assertSee('Opinión conservada de prueba.');
            $this->assertCount(0, $this->document($response->getContent())->query('//header[contains(concat(" ", normalize-space(@class), " "), " is-sticky ")]'));
        }
        $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk()->assertViewHas('data', static fn (array $hydrated): bool =>
            $hydrated['button_icon'] === 'arrow-right' && $hydrated['text_alignment'] === 'section'
            && $hydrated['stories'][0]['media_type'] === 'image' && $hydrated['stories'][0]['video_url'] === ''
            && $hydrated['testimonials'][0]['video_poster'] === '' && $hydrated['sections'][0]['alignment'] === 'center');
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }

    public function test_video_testimonials_without_transcription_and_stories_without_a_poster_remain_visible(): void
    {
        $data = $this->mediaContent();
        $data['testimonials'][0]['quote'] = '';
        $data['stories'][0]['video_poster'] = '';
        $landing = $this->landing($data, published: true);

        $response = $this->get('/lp/'.$landing->slug)->assertOk()
            ->assertSee('Pareja de prueba')->assertSee('Historia audiovisual de prueba')
            ->assertSee('testimonio.mp4', false)->assertSee('historia-portada.mp4', false);
        $xpath = $this->document($response->getContent());
        $this->assertCount(1, $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " section-testimonials ")]//video'));
        $this->assertCount(0, $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " section-testimonials ")]//blockquote[normalize-space(.)=""]'));
        $this->assertCount(1, $xpath->query('//button[@data-story-open]'));
    }

    public function test_invalid_media_and_icon_settings_are_rejected_without_saving(): void
    {
        $data = $this->mediaContent();
        $landing = $this->landing($data);
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());
        foreach ([
            'button_icon' => '<svg onload=alert(1)>',
            'text_alignment' => 'center; color:red',
            'stories.0.media_type' => 'iframe',
            'stories.0.video_url' => 'javascript:alert(1)',
            'stories.0.images.1.video_url' => 'data:video/mp4;base64,unsafe',
            'testimonials.0.video_poster' => 'https://example.test/no-permitida.jpg',
            'experiences.0.video_url' => 'http://example.test/video-inseguro.mp4',
            'gallery.0.video_url' => '//example.test/video.mp4',
            'sections.0.video_url' => 'javascript:alert(1)',
        ] as $path => $invalid) {
            $input = $data;
            data_set($input, $path, $invalid);
            $this->putJson('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => 1, 'data' => $input,
            ])->assertUnprocessable()->assertJsonValidationErrors('data.'.$path);
            $this->assertSame($before, $landing->fresh()->getRawOriginal());
        }
    }
}
