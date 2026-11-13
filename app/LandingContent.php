<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LandingContent
{
    /** @return array<string, mixed> */
    public function defaults(string $preset = 'editorial'): array
    {
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

        return $this->castValues($this->normalize($validated, $this->fields()), $this->fields());
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
                'phone' => ['nullable', 'string', 'regex:/^[0-9]{7,15}$/'],
                'image' => ['nullable', 'string', 'max:500', 'regex:#^(?:/?assets/images/[a-zA-Z0-9/_.-]+|/media/[0-9]+)$#'],
                'video' => ['nullable', 'string', 'max:1000', 'regex:#^(?:/media/[0-9]+|https://[^\s<>"\x27]+)$#'],
                'link' => ['nullable', 'string', 'max:500', 'regex:#^(?:/(?!/)[a-zA-Z0-9/_?=&.%-]*|\#[a-zA-Z0-9_-]+|https?://[^\s<>"\x27]+)$#'],
                'repeater' => ['present', 'array', 'min:'.$field['min'], 'max:'.$field['max']],
                default => ['nullable', 'string', 'max:'.($field['type'] === 'textarea' ? 5000 : 500)],
            };
            if ($field['type'] === 'repeater') {
                $rules = [...$rules, ...$this->rules($field['fields'], $path.'.*')];
            }
        }

        return $rules;
    }
}
