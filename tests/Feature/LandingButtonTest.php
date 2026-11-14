<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LandingButtonTest extends TestCase
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

    /** @param array<string, mixed> $overrides */
    private function landing(string $slug = 'botones-de-prueba', bool $published = false, array $overrides = []): LandingPage
    {
        $data = array_replace(config('landing.defaults'), $overrides);

        return LandingPage::create([
            'name' => 'Campaña de botones de prueba', 'slug' => $slug,
            'draft' => $data, 'published' => $published ? $data : null,
            'revision' => 1, 'published_at' => $published ? now() : null,
        ]);
    }

    /** @return array<int, string> */
    private function bodyClasses(string $html): array
    {
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        return preg_split('/\s+/', trim($document->getElementsByTagName('body')->item(0)->getAttribute('class')));
    }

    public function test_button_models_and_animations_can_be_saved_previewed_and_published_independently(): void
    {
        $styles = ['solid', 'outline', 'gradient', 'soft', 'glass', 'raised', 'neon'];
        $animations = ['none', 'lift', 'pulse', 'shine', 'glow', 'bounce', 'ripple', 'sweep'];
        $sizes = ['compact', 'regular', 'large'];
        $durations = [2, 3, 4, 5, 6, 7, 8, 10];
        $this->actingAs($this->owner());

        foreach ($animations as $index => $animation) {
            $style = $styles[$index % count($styles)];
            $size = $sizes[$index % count($sizes)];
            $landing = $this->landing('botones-de-prueba-'.$index);
            $data = array_replace($landing->draft, [
                'button_style' => $style, 'button_animation' => $animation, 'button_size' => $size,
                'button_secondary_color' => '#a65c39', 'button_animation_speed' => (string) $durations[$index],
                'button_label' => 'Mi propuesta <personalizada>',
                'hero_layout' => $index % 2 === 0 ? 'split' : 'full',
            ]);
            $data['sections'][0]['button_url'] = 'https://wa.me/51987654321?text=Propuesta%20de%20prueba';

            $this->put('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => 1, 'data' => $data,
            ])->assertRedirect()->assertSessionHasNoErrors();
            $landing->refresh();
            foreach (['button_style' => $style, 'button_animation' => $animation, 'button_size' => $size, 'button_secondary_color' => '#a65c39', 'button_animation_speed' => $durations[$index]] as $field => $value) {
                $this->assertSame($value, $landing->draft[$field]);
            }

            $preview = $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk();
            $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => $landing->revision])
                ->assertRedirect()->assertSessionHasNoErrors();
            $public = $this->get('/lp/'.$landing->slug)->assertOk();
            if (getenv('LANDING_CAPTURE')) {
                File::ensureDirectoryExists(storage_path('app/visual-review'));
                file_put_contents(storage_path('app/visual-review/landing-button-'.$style.'-'.$animation.'.html'), $public->getContent());
            }

            foreach ([$preview, $public] as $response) {
                $classes = $this->bodyClasses($response->getContent());
                $this->assertContains('button-'.$style, $classes);
                $this->assertContains('button-animation-'.$animation, $classes);
                $this->assertContains('button-size-'.$size, $classes);
                $response->assertSee($data['button_label'])->assertDontSee('<personalizada>', false)
                    ->assertSee('href="'.$data['sections'][0]['button_url'].'"', false);
            }
            $this->assertSame($landing->draft, $landing->fresh()->published);
        }
    }

    public function test_unsaved_button_preview_keeps_the_published_appearance_and_saved_content_unchanged(): void
    {
        $landing = $this->landing(published: true);
        $before = $landing->fresh()->getRawOriginal();
        $data = array_replace($landing->draft, [
            'button_style' => 'gradient', 'button_animation' => 'sweep', 'button_size' => 'large',
            'button_secondary_color' => '#cc8844', 'button_animation_speed' => 4,
        ]);

        $preview = $this->actingAs($this->owner())->postJson('/admin/landings/'.$landing->id.'/preview', ['data' => $data])->assertOk();
        $previewClasses = $this->bodyClasses($preview->getContent());
        $this->assertContains('button-gradient', $previewClasses);
        $this->assertContains('button-animation-sweep', $previewClasses);
        $this->assertContains('button-size-large', $previewClasses);

        $public = $this->get('/lp/'.$landing->slug)->assertOk();
        $publicClasses = $this->bodyClasses($public->getContent());
        $this->assertContains('button-'.$landing->published['button_style'], $publicClasses);
        $this->assertContains('button-animation-'.$landing->published['button_animation'], $publicClasses);
        $this->assertNotContains('button-gradient', $publicClasses);
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }

    public function test_existing_button_settings_get_new_defaults_without_rewriting_older_campaigns(): void
    {
        $landing = $this->landing(published: true, overrides: ['button_style' => 'outline', 'button_animation' => 'pulse']);
        $legacyData = $landing->draft;
        unset($legacyData['button_secondary_color'], $legacyData['button_size'], $legacyData['button_animation_speed']);
        $landing->update(['draft' => $legacyData, 'published' => $legacyData]);
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());

        foreach ([$this->get('/lp/'.$landing->slug)->assertOk(), $this->get('/admin/landings/'.$landing->id.'/preview')->assertOk()] as $response) {
            $classes = $this->bodyClasses($response->getContent());
            $this->assertContains('button-outline', $classes);
            $this->assertContains('button-animation-pulse', $classes);
            $this->assertContains('button-size-regular', $classes);
        }
        $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk()
            ->assertViewHas('data', fn (array $data): bool => $data['button_secondary_color'] === '#8b642e'
                && $data['button_size'] === 'regular' && $data['button_animation_speed'] === 6)
            ->assertSee('value="gradient"', false)->assertSee('value="sweep"', false);
        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }

    public function test_invalid_button_models_colors_and_animation_settings_are_rejected_without_saving(): void
    {
        $landing = $this->landing();
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());

        foreach ([
            ['button_style' => 'gradient; color:red'],
            ['button_animation' => 'javascript:alert(1)'],
            ['button_size' => 'giant'],
            ['button_secondary_color' => '#fff'],
            ['button_secondary_color' => '#fff; background:url(https://example.test)'],
            ['button_animation_speed' => 1],
            ['button_animation_speed' => 11],
            ['button_animation_speed' => 'fast'],
        ] as $invalid) {
            $this->putJson('/admin/landings/'.$landing->id, [
                'name' => $landing->name, 'revision' => 1, 'data' => array_replace($landing->draft, $invalid),
            ])->assertUnprocessable()->assertJsonValidationErrors('data.'.array_key_first($invalid));
        }

        $this->assertSame($before, $landing->fresh()->getRawOriginal());
    }
}
