<?php

namespace App;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LandingContent
{
    /** @return array<string, mixed> */
    public function defaults(string $preset = 'editorial'): array
    {
        if ($preset === 'wedding') {
            return $this->weddingDraft([]);
        }

        $data = config('landing.defaults');
        if ($preset === 'modern') {
            $data = array_replace($data, [
                'heading_font' => 'DM Sans', 'heading_size' => 64, 'background_color' => '#ffffff',
                'text_color' => '#202528', 'accent_color' => '#25474a', 'button_radius' => 30,
                'hero_layout' => 'full', 'animation' => 'fade',
            ]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function weddingDraft(array $existing): array
    {
        $isNew = $existing === [];
        $data = $this->hydrate($isNew ? config('landing.defaults') : $existing);
        $site = app(SiteContent::class);
        if ($data['brand_name'] === config('editor.sections.branding.defaults.brand')) {
            $oldBrand = $data['brand_name'];
            $data['brand_name'] = 'Renzo Núñez Melgar';
            if ($data['page_title'] === 'Fotógrafo de bodas en Arequipa · '.$oldBrand) {
                $data['page_title'] = 'Fotógrafo de bodas en Arequipa · '.$data['brand_name'];
            }
        }
        $data = array_replace($data, [
            'visual_theme' => 'wedding', 'heading_font' => 'Playfair Display', 'body_font' => 'DM Sans',
            'background_color' => '#f8f5ef', 'text_color' => '#272522', 'accent_color' => '#39352f',
            'button_text_color' => '#ffffff', 'button_secondary_color' => '#b89d75',
            'button_radius' => 0, 'button_style' => 'solid', 'button_animation' => 'lift',
            'animation' => 'fade', 'light_effect' => 'none', 'image_motion' => 'none',
            'hero_layout' => 'full', 'hero_mobile_layout' => 'editorial', 'sticky_header' => false, 'form_two_step' => true,
            'show_location' => true, 'show_experience' => true,
        ]);

        $fillIfBlank = static function (string $key, mixed $value) use (&$data): void {
            if (blank($data[$key] ?? null)) {
                $data[$key] = $value;
            }
        };
        $fillIfBlank('brand_logo', $site->get('branding.logo', ''));
        $fillIfBlank('brand_tagline', 'FOTOGRAFÍA DOCUMENTAL · PERÚ');
        $fillIfBlank('contact_email', $site->get('branding.email', ''));
        $fillIfBlank('contact_location', $site->get('branding.location', ''));
        $phone = (string) $site->get('branding.phone_link', '');
        if ($phone !== '51900000000' && preg_match('/^[0-9]{7,15}$/', $phone) === 1) {
            $fillIfBlank('whatsapp', $phone);
        }
        $instagram = (string) $site->get('branding.instagram', '');
        if (! in_array(rtrim($instagram, '/'), ['', 'https://instagram.com', 'https://www.instagram.com'], true)) {
            $fillIfBlank('instagram_url', $instagram);
        }
        $fillIfBlank('hero_secondary_label', 'Descubrir historias reales');
        $fillIfBlank('hero_secondary_url', '#historias');
        $fillIfBlank('nav_links', [
            ['label' => 'Historias', 'url' => '#historias', 'enabled' => true],
            ['label' => 'La experiencia', 'url' => '#experiencias', 'enabled' => true],
            ['label' => 'Conversemos', 'url' => '#contacto', 'enabled' => true],
        ]);
        $fillIfBlank('about_bio', trim($site->get('about_about_hero.field_4', '')."\n\n".$site->get('about_about_hero.field_5', '')));
        $fillIfBlank('about_philosophy', $site->get('about_about_story.field_2', ''));

        if ($data['stories'] === []) {
            $data['stories'] = collect($site->get('portfolio_items.items', []))->take(18)->map(static fn (array $item): array => [
                'names' => $item['names'] ?? '', 'title' => $item['tag'] ?? '',
                'location' => $item['location'] ?? '', 'date' => '', 'description' => '',
                'image' => $item['image'] ?? '', 'alt' => 'Fotografía de '.($item['names'] ?? 'boda'),
                'enabled' => true, 'images' => [],
            ])->all();
        }
        if ($data['experiences'] === []) {
            $data['experiences'] = collect($site->services())->take(12)->map(static fn (array $service): array => [
                'name' => $service['title'], 'subtitle' => $service['tag'] ?? '',
                'description' => $service['summary'] ?? '', 'image' => $service['image'] ?? '',
                'alt' => $service['image_alt'] ?? '', 'inclusions' => '', 'hours' => 0,
                'price' => 0, 'show_price' => false, 'button_label' => 'Consultar esta experiencia',
                'button_url' => '#contacto', 'featured' => false, 'enabled' => true,
            ])->values()->all();
        }

        $publishedTestimonials = SiteSetting::where('section', 'testimonials')->first()?->published;
        if ($data['testimonials'] === [] && ! empty($publishedTestimonials['items'])) {
            $data['testimonials'] = collect($publishedTestimonials['items'])->take(12)->map(static fn (array $item): array => [
                'name' => $item['names'] ?? '', 'quote' => $item['quote'] ?? '', 'role' => $item['detail'] ?? '',
                'image' => '', 'location' => '', 'date' => '', 'rating' => 0, 'review_url' => '', 'enabled' => true,
            ])->all();
        }
        $publishedExperience = SiteSetting::where('section', 'home_experience')->first()?->published;
        if ($data['trust_metrics'] === [] && $publishedExperience) {
            foreach (['weddings' => 'field_1', 'years' => 'field_2'] as $valueKey => $labelKey) {
                if (filled($publishedExperience[$valueKey] ?? null)) {
                    $data['trust_metrics'][] = [
                        'value' => (string) $publishedExperience[$valueKey],
                        'label' => (string) ($publishedExperience[$labelKey] ?? ''), 'enabled' => true,
                    ];
                }
            }
        }
        $fillIfBlank('process_steps', [
            ['title' => 'Nos conocemos', 'text' => 'Cuéntennos qué imaginan para su boda y qué recuerdos quieren conservar.', 'enabled' => true],
            ['title' => 'Conversamos sobre su boda', 'text' => 'Revisamos la fecha, la locación y los momentos que más les importan.', 'enabled' => true],
            ['title' => 'Una propuesta personalizada', 'text' => 'Definimos juntos una cobertura que acompañe su historia.', 'enabled' => true],
            ['title' => 'Confirmamos los detalles', 'text' => 'Las condiciones de reserva se acuerdan directamente con el estudio.', 'enabled' => true],
            ['title' => 'Documentamos su historia', 'text' => 'Una mirada atenta a lo que sucede, a los encuentros y a la emoción.', 'enabled' => true],
            ['title' => 'Sus recuerdos permanecen', 'text' => 'La entrega y sus detalles se confirman en su propuesta personalizada.', 'enabled' => true],
        ]);

        $section = static fn (string $type, string $eyebrow, string $title, string $text = '', string $image = ''): array => [
            'type' => $type, 'enabled' => true, 'eyebrow' => $eyebrow, 'title' => $title, 'text' => $text,
            'image' => $image, 'alt' => '', 'button_label' => '', 'button_url' => '#contacto',
            'custom_background' => false, 'background' => '#f2ede5', 'padding' => 64, 'alignment' => 'left',
        ];
        $newSections = [
            'hero' => $section('hero', 'FOTOGRAFÍA DOCUMENTAL DE BODAS · PERÚ', "El día pasa.\nLa historia permanece.", 'Fotografía de bodas para parejas que quieren recordar no solo cómo fue su día, sino cómo se sintió.', 'assets/images/hero/hero-01.webp'),
            'trust' => $section('trust', 'UNA MIRADA QUE LOS ACOMPAÑA', 'Historias que hablan por sí mismas.'),
            'stories' => $section('stories', 'HISTORIAS REALES', 'Cada historia tiene su propia luz.', 'Una selección del portafolio del estudio.'),
            'text' => $section('text', 'DOCUMENTAL · EDITORIAL · ATEMPORAL', 'No fotografío bodas. Cuento historias que merecen permanecer.', $site->get('home_about_philosophy.field_9', ''), 'assets/images/details/rings.webp'),
            'about' => $section('about', 'CONOCE A RENZO', 'La mirada detrás de su historia.'),
            'experiences' => $section('experiences', 'EXPERIENCIAS DE BODA', 'Una experiencia para cada historia.', 'La fecha, el lugar y su forma de celebrar son el punto de partida.'),
            'testimonials' => $section('testimonials', 'SUS PALABRAS', 'Lo que permanece también se siente.'),
            'process' => $section('process', 'CÓMO NOS ACOMPAÑAMOS', 'De una conversación a un recuerdo.'),
            'offer' => $section('offer', 'UNA PROPUESTA A SU MEDIDA', 'Cada historia merece una experiencia a su medida.', 'Conversemos sobre su celebración para preparar una propuesta personalizada.'),
            'faq' => $section('faq', 'ANTES DE CONTAR SU HISTORIA', 'Conversemos con calma.'),
            'lead' => $section('lead', 'CONSULTA PERSONALIZADA', 'Todo comienza con una fecha.', 'Cuéntenme un poco sobre su boda. Me encantaría conocer su historia.'),
            'closing' => $section('closing', 'RECUERDOS QUE PERMANECEN', 'El tiempo pasa. Los recuerdos permanecen.', 'Me encantaría contar su historia.', 'assets/images/hero/hero-03.webp'),
        ];
        if ($isNew) {
            $data['brand_name'] = $site->get('branding.brand', 'Renzo Núñez Melgar');
            if ($data['brand_name'] === config('editor.sections.branding.defaults.brand')) {
                $data['brand_name'] = 'Renzo Núñez Melgar';
            }
            $data['brand_tagline'] = 'FOTOGRAFÍA DOCUMENTAL · PERÚ';
            $data['page_title'] = 'Fotógrafo de bodas en Arequipa · '.$data['brand_name'];
            $data['meta_description'] = 'Fotografía documental y editorial de bodas en Arequipa y Perú. Descubre las historias de Renzo y consulta una propuesta personalizada para tu fecha.';
            $data['button_label'] = 'Consultar disponibilidad';
            $data['submit_label'] = 'Consultar disponibilidad de mi boda';
            $data['success_message'] = 'Gracias por compartir su historia. Recibimos su consulta. El estudio revisará su fecha y se pondrá en contacto; este envío no confirma disponibilidad ni reserva.';
            $data['form_note'] = 'Una conversación, sin compromiso. La disponibilidad se confirma personalmente.';
            $data['sections'] = array_values($newSections);
        } else {
            $ordered = [];
            foreach ($newSections as $type => $template) {
                $matches = array_filter($data['sections'], static fn (array $item): bool => $item['type'] === $type);
                $ordered = [...$ordered, ...($matches ?: [$template])];
                $relatedType = match ($type) {
                    'stories' => 'gallery',
                    'text' => 'benefits',
                    default => null,
                };
                if ($relatedType) {
                    $ordered = [...$ordered, ...array_filter($data['sections'], static fn (array $item): bool => $item['type'] === $relatedType)];
                }
            }
            foreach ($data['sections'] as $item) {
                if (! isset($newSections[$item['type']]) && ! in_array($item['type'], ['gallery', 'benefits'], true)) {
                    $ordered[] = $item;
                }
            }
            $data['sections'] = $ordered;
        }

        return $this->hydrate($data);
    }

    /** @return array<string, array<string, mixed>> */
    public function fields(): array
    {
        $fields = [];
        foreach (config('landing.groups') as $group) {
            $fields = [...$fields, ...$group['fields']];
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function hydrate(array $data): array
    {
        return $this->hydrateFields($data, $this->fields(), config('landing.defaults'));
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private function hydrateFields(array $values, array $fields, array $defaults = []): array
    {
        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $values)) {
                $values[$key] = $defaults[$key] ?? $field['default'] ?? match ($field['type']) {
                    'repeater' => [],
                    'boolean' => false,
                    default => '',
                };
            }
            if ($field['type'] === 'repeater' && is_array($values[$key])) {
                $values[$key] = array_map(fn (array $item): array => $this->hydrateFields($item, $field['fields']), $values[$key]);
            }
        }

        return $values;
    }

    /** @param array<string, mixed> $data */
    public function primaryButtonUrl(array $data): string
    {
        foreach ($data['sections'] as $section) {
            if ($section['type'] === 'hero' && $section['enabled']) {
                return $section['button_url'] ?: '#contacto';
            }
        }

        return '#contacto';
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function validate(array $input): array
    {
        $data = $this->normalize($input, $this->fields());
        $validated = Validator::make(['data' => $data], $this->rules($this->fields()), [
            'required' => 'Completa el campo :attribute.',
            'regex' => 'El formato del campo :attribute no es válido.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'array' => 'Revisa los campos permitidos en :attribute.',
            'max' => 'El campo :attribute supera el límite permitido (:max).',
            'min' => 'El campo :attribute debe ser como mínimo :min.',
        ])->validate()['data'];

        return array_replace($this->castValues($this->normalize($validated, $this->fields()), $this->fields()), [
            'sticky_header' => false, 'hero_layout' => 'full', 'hero_mobile_layout' => 'editorial',
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function castValues(array $values, array $fields): array
    {
        foreach ($fields as $key => $field) {
            $values[$key] = match ($field['type']) {
                'boolean' => (bool) $values[$key],
                'number' => (int) $values[$key],
                'decimal' => (float) $values[$key],
                'repeater' => array_map(fn (array $item): array => $this->castValues($item, $field['fields']), $values[$key]),
                default => $values[$key],
            };
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function normalize(array $input, array $fields): array
    {
        foreach ($fields as $key => $field) {
            if ($field['type'] === 'repeater') {
                $items = $input[$key] ?? [];
                $input[$key] = is_array($items) ? array_values(array_map(
                    fn (mixed $item): mixed => is_array($item) ? $this->normalize($item, $field['fields']) : $item,
                    $items,
                )) : $items;
            } elseif (! array_key_exists($key, $input) || $input[$key] === null) {
                $input[$key] = $field['default'] ?? ($field['type'] === 'boolean' ? false : '');
            }
        }

        return $input;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $fields, string $prefix = 'data'): array
    {
        $rules = [$prefix => ['required', 'array:'.implode(',', array_keys($fields))]];
        foreach ($fields as $key => $field) {
            $path = $prefix.'.'.$key;
            $rules[$path] = match ($field['type']) {
                'boolean' => ['required', 'boolean'],
                'color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
                'number' => ['required', 'integer', 'min:'.$field['min'], 'max:'.$field['max']],
                'decimal' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
                'font' => ['required', Rule::in(array_keys(config('editor.fonts')))],
                'select' => ['required', Rule::in(array_keys($field['options']))],
                'pixel' => ['nullable', 'string', 'regex:/^[0-9]{5,25}$/'],
                'ga4' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,20}$/'],
                'email' => ['nullable', 'email:rfc', 'max:160'],
                'phone' => ['nullable', 'string', 'regex:/^[0-9]{7,15}$/'],
                'image' => ['nullable', 'string', 'max:500', 'regex:#^(?:/?assets/images/[a-zA-Z0-9/_.-]+|/media/[0-9]+)$#'],
                'video' => ['nullable', 'string', 'max:1000', 'regex:#^(?:/media/[0-9]+|https://[^\s<>"\x27]+)$#'],
                'link' => ['nullable', 'string', 'max:500', 'regex:#^(?:/(?!/)[a-zA-Z0-9/_?=&.%-]*|\#[a-zA-Z0-9_-]+|https?://[^\s<>"\x27]+)$#'],
                'repeater' => ['present', 'array', 'min:'.$field['min'], 'max:'.$field['max']],
                default => ['nullable', 'string', 'max:'.($field['max_length'] ?? ($field['type'] === 'textarea' ? 5000 : 500))],
            };
            if ($field['type'] === 'repeater') {
                $rules = [...$rules, ...$this->rules($field['fields'], $path.'.*')];
            }
        }

        return $rules;
    }
}
