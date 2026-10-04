<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_pages_are_available(): void
    {
        $pages = [
            '/',
            '/sobre-mi',
            '/portafolio',
            '/servicios',
            '/servicios/cobertura-boda',
            '/servicios/preboda',
            '/servicios/postboda',
            '/contacto',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from('/contacto')
            ->post('/contacto', [])
            ->assertRedirect('/contacto')
            ->assertSessionHasErrors([
                'couple_names',
                'email',
                'phone',
                'event_date',
                'location',
                'service',
                'message',
            ]);
    }

    public function test_contact_form_accepts_a_valid_inquiry(): void
    {
        $this->from('/contacto')
            ->post('/contacto', [
                'couple_names' => 'Lucía & Mateo',
                'email' => 'pareja@example.com',
                'phone' => '+51 900 000 000',
                'event_date' => now()->addMonth()->toDateString(),
                'location' => 'Arequipa',
                'service' => 'wedding',
                'message' => 'Queremos documentar nuestra boda con un estilo editorial y natural.',
            ])
            ->assertRedirect('/contacto')
            ->assertSessionHas('success');
    }
}
