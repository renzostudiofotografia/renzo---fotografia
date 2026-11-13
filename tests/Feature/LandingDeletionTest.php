<?php

namespace Tests\Feature;

use App\Models\LandingLead;
use App\Models\LandingPage;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class LandingDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function landing(string $slug = 'campana-eliminar', bool $published = false): LandingPage
    {
        $data = config('landing.defaults');

        return LandingPage::create([
            'name' => 'Campaña de prueba '.$slug, 'slug' => $slug,
            'draft' => $data, 'published' => $published ? $data : null,
            'revision' => 1, 'published_at' => $published ? now() : null,
        ]);
    }

    private function lead(LandingPage $landing): LandingLead
    {
        return LandingLead::create([
            'landing_page_id' => $landing->id, 'submission_id' => (string) Str::uuid(),
            'name' => 'Contacto de prueba', 'phone' => '51987654321', 'consent_at' => now(),
        ]);
    }

    public function test_deleting_and_restoring_landings_require_an_authenticated_owner(): void
    {
        $active = $this->landing();
        $trashed = $this->landing('campana-en-papelera');
        $trashed->delete();

        $this->delete('/admin/landings/'.$active->id, ['revision' => 1, 'confirmed' => 1])
            ->assertRedirect('/admin/login');
        $this->post('/admin/landings/'.$trashed->id.'/restore', ['revision' => 1])
            ->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create());
        $this->deleteJson('/admin/landings/'.$active->id, ['revision' => 1, 'confirmed' => 1])->assertForbidden();
        $this->postJson('/admin/landings/'.$trashed->id.'/restore', ['revision' => 1])->assertForbidden();
        $this->get('/admin/landings?trash=1')->assertForbidden();
        $this->assertFalse($active->fresh()->trashed());
        $this->assertSoftDeleted('landing_pages', ['id' => $trashed->id]);
    }

    public function test_deletion_requires_confirmation_and_cannot_overwrite_a_newer_revision(): void
    {
        $landing = $this->landing(published: true);
        $before = $landing->fresh()->getRawOriginal();
        $this->actingAs($this->owner());

        $this->deleteJson('/admin/landings/'.$landing->id, ['revision' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->deleteJson('/admin/landings/'.$landing->id, ['revision' => 1, 'confirmed' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->deleteJson('/admin/landings/'.$landing->id, ['revision' => 2, 'confirmed' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('revision');

        $this->assertSame($before, $landing->fresh()->getRawOriginal());
        $this->get('/lp/'.$landing->slug)->assertOk();
    }

    public function test_deleting_a_published_landing_removes_public_access_and_preserves_contacts_and_shared_media(): void
    {
        Storage::fake('local')->put('site-media/shared.jpg', 'shared-photo-content');
        $asset = MediaAsset::create([
            'name' => 'Fotografía compartida de prueba', 'alt' => '', 'provider' => 'local',
            'path' => 'site-media/shared.jpg', 'mime_type' => 'image/jpeg', 'size' => 20,
        ]);
        $landing = $this->landing(published: true);
        $other = $this->landing('otra-campana', true);
        foreach ([$landing, $other] as $page) {
            $data = $page->draft;
            $data['sections'][0]['image'] = '/media/'.$asset->id;
            $page->update(['draft' => $data, 'published' => $data]);
        }
        $draft = $landing->draft;
        $published = $landing->published;
        $contact = $this->lead($landing);
        $otherContact = $this->lead($other);

        $this->actingAs($this->owner())->delete('/admin/landings/'.$landing->id, ['revision' => 1, 'confirmed' => 1])
            ->assertRedirect(route('admin.landings.index'))->assertSessionHasNoErrors();

        $this->assertSoftDeleted('landing_pages', ['id' => $landing->id]);
        $deleted = LandingPage::withTrashed()->findOrFail($landing->id);
        $this->assertSame(2, $deleted->revision);
        $this->assertSame($draft, $deleted->draft);
        $this->assertSame($published, $deleted->published);
        $this->assertDatabaseHas('landing_leads', ['id' => $contact->id, 'landing_page_id' => $landing->id]);
        $this->assertDatabaseHas('landing_leads', ['id' => $otherContact->id, 'landing_page_id' => $other->id]);
        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
        Storage::disk('local')->assertExists($asset->path);

        $this->get('/lp/'.$landing->slug)->assertNotFound();
        $this->postJson('/lp/'.$landing->slug.'/leads', [])->assertNotFound();
        foreach (['edit', 'preview', 'leads'] as $page) {
            $this->get('/admin/landings/'.$landing->id.'/'.$page)->assertNotFound();
        }
        $this->get('/lp/'.$other->slug)->assertOk();
        $this->assertSame(1, $other->fresh()->revision);
    }

    public function test_active_and_trashed_lists_are_separate_and_expose_the_appropriate_actions(): void
    {
        $active = $this->landing('campana-activa');
        $trashed = $this->landing('campana-descartada');
        $trashed->delete();
        $this->actingAs($this->owner());

        $this->get('/admin/landings')->assertOk()
            ->assertDontSee('@endsection')
            ->assertViewHas('showTrash', false)
            ->assertViewHas('activeCount', 1)->assertViewHas('trashCount', 1)
            ->assertViewHas('landings', fn ($pages): bool => $pages->getCollection()->pluck('id')->all() === [$active->id])
            ->assertSee('Eliminar landing')->assertDontSee($trashed->name);
        $this->get('/admin/landings/'.$active->id.'/edit')->assertOk()->assertSee('Eliminar landing');

        $this->get('/admin/landings?trash=1')->assertOk()
            ->assertDontSee('@endsection')
            ->assertViewHas('showTrash', true)
            ->assertViewHas('landings', fn ($pages): bool => $pages->getCollection()->pluck('id')->all() === [$trashed->id])
            ->assertSee('Restaurar como borrador')->assertDontSee($active->name)
            ->assertDontSee('href="'.route('admin.landings.edit', $trashed).'"', false);
    }

    public function test_restoration_checks_the_revision_and_returns_the_landing_as_a_private_draft_with_its_contacts(): void
    {
        $landing = $this->landing(published: true);
        $contact = $this->lead($landing);
        $draft = $landing->draft;
        $this->actingAs($this->owner());
        $this->post('/admin/landings/'.$landing->id.'/restore', ['revision' => 1])->assertNotFound();
        $this->delete('/admin/landings/'.$landing->id, ['revision' => 1, 'confirmed' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->postJson('/admin/landings/'.$landing->id.'/restore', ['revision' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('revision');
        $this->assertSoftDeleted('landing_pages', ['id' => $landing->id, 'revision' => 2]);
        $this->post('/admin/landings/'.$landing->id.'/restore', ['revision' => 2])
            ->assertRedirect(route('admin.landings.edit', $landing))->assertSessionHasNoErrors();

        $restored = LandingPage::findOrFail($landing->id);
        $this->assertFalse($restored->trashed());
        $this->assertSame(3, $restored->revision);
        $this->assertSame($draft, $restored->draft);
        $this->assertNull($restored->published);
        $this->assertNull($restored->published_at);
        $this->assertDatabaseHas('landing_leads', ['id' => $contact->id, 'landing_page_id' => $landing->id]);
        $this->get('/lp/'.$landing->slug)->assertNotFound();
        $this->postJson('/lp/'.$landing->slug.'/leads', [])->assertNotFound();
        $this->get('/admin/landings/'.$landing->id.'/edit')->assertOk();
        $this->post('/admin/landings/'.$landing->id.'/publish', ['revision' => 3])->assertSessionHasNoErrors();
        $this->get('/lp/'.$landing->slug)->assertOk();
    }

    public function test_a_trashed_landing_keeps_its_campaign_url_reserved(): void
    {
        $landing = $this->landing();
        $landing->delete();

        $this->actingAs($this->owner())->post('/admin/landings', [
            'name' => 'Otra campaña de prueba', 'slug' => $landing->slug, 'preset' => 'editorial',
        ])->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('landing_pages', 1);
        $this->assertSoftDeleted('landing_pages', ['id' => $landing->id]);
    }

    public function test_the_listing_can_render_older_controller_data_without_trash_metadata(): void
    {
        $landing = $this->landing();
        $this->actingAs($this->owner());

        $html = view('admin.landings', [
            'landings' => LandingPage::withCount('leads')->paginate(12),
            'pendingCount' => 0, 'errors' => new ViewErrorBag,
        ])->render();

        $this->assertStringContainsString($landing->name, $html);
        $this->assertStringContainsString('Papelera', $html);
        $this->assertStringContainsString('Crear landing', $html);
        $this->assertStringNotContainsString('@endsection', $html);
    }
}
