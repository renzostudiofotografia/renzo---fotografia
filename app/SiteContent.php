<?php

namespace App;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class SiteContent
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $content = null;

    private bool $loadedPreview = false;

    public function forget(): void
    {
        $this->content = null;
    }

    /** @return array<string, array<string, mixed>> */
    public function definitions(): array
    {
        return config('editor.sections');
    }

    public function isPreview(): bool
    {
        return request()->boolean('editor_preview') && (bool) request()->user()?->is_admin;
    }

    public function previewSection(): ?string
    {
        $section = request()->input('editor_section');

        return $this->isPreview() && is_string($section) && isset($this->definitions()[$section]) ? $section : null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->content === null || $this->loadedPreview !== $this->isPreview()) {
            $this->loadedPreview = $this->isPreview();
            $this->content = [];
            foreach ($this->definitions() as $section => $definition) {
                $this->content[$section] = $definition['defaults'];
            }
            if (Schema::hasTable('site_settings')) {
                foreach (SiteSetting::all() as $setting) {
                    if (isset($this->content[$setting->section])) {
                        $values = $this->isPreview() ? $setting->draft : $setting->published;
                        $this->content[$setting->section] = array_replace($this->content[$setting->section], $values ?? []);
                    }
                }
            }
        }

        return data_get($this->content, $key, $default);
    }

    /** @return array<int, string> */
    public function pageSections(string $page): array
    {
        return collect(config('editor.pages.'.$page, []))
            ->filter(fn (string $section): bool => (bool) $this->get($section.'.enabled', true) || $this->previewSection() === $section)
            ->sortBy(fn (string $section): float => (float) $this->get($section.'.position', 0))
            ->values()->all();
    }

    /** @return array<string, array<string, mixed>> */
    public function services(): array
    {
        $services = [];
        foreach (config('site.services') as $key => $original) {
            $services[$key] = array_replace($original, $this->get('service_'.$key, []));
        }

        return $services;
    }

    public function fontUrl(): string
    {
        $fonts = config('editor.fonts');
        $heading = $this->get('typography.heading_font');
        $body = $this->get('typography.body_font');

        return 'https://fonts.googleapis.com/css2?family='.implode('&family=', array_unique([
            $fonts[$heading] ?? $fonts['Playfair Display'],
            $fonts[$body] ?? $fonts['Cormorant Garamond'],
        ])).'&display=swap';
    }

    public function sectionStyle(string $section): string
    {
        $styles = [];
        if ($this->get($section.'.custom_background', false)) {
            $styles[] = 'background-color:'.$this->get($section.'.section_background');
        }
        if ($this->get($section.'.custom_spacing', false)) {
            $styles[] = 'padding-top:'.$this->get($section.'.padding_top').'px';
            $styles[] = 'padding-bottom:'.$this->get($section.'.padding_bottom').'px';
        }

        return implode(';', $styles);
    }
}
