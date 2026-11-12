@extends('admin.layout')
@section('title', 'Editor del sitio')
@section('body-class', 'editor-layout')
@section('save-action')<button class="button secondary" type="submit" form="section-form">Guardar borrador</button>@endsection
@section('content')
@php
    $previewPaths = ['home' => '/', 'about' => '/sobre-mi', 'portfolio' => '/portafolio', 'services' => '/servicios', 'contact' => '/contacto'];
    $previewPath = $previewPaths[$definition['page'] ?? 'home'] ?? '/';
    if ($section === 'portfolio_items') { $previewPath = '/portafolio'; }
    if ($section === 'booking') { $previewPath = '/citas'; }
    if (str_starts_with($section, 'service_')) { $previewPath = '/servicios/'.config('site.services.'.substr($section, 8).'.slug'); }
    $previewSection = match ($section) {
        'portfolio_items' => 'portfolio_portfolio_page_section',
        'testimonials' => 'home_testimonials',
        default => $section,
    };
    $previewUrl = url($previewPath).'?'.http_build_query(['editor_preview' => 1, 'editor_section' => $previewSection]);
@endphp
<main class="editor-workspace" data-editor-root="{{ url('/admin/editor') }}">
    <aside class="sections-panel" aria-label="Secciones del sitio"><div class="panel-heading"><span class="eyebrow">PERSONALIZAR</span><h2>Tu sitio, por secciones</h2><p>Elige lo que quieres cambiar.</p></div>
        <div class="section-groups">@foreach(collect($definitions)->groupBy('group', true) as $group => $groupDefinitions)
            <details @if($group === $definition['group']) open @endif><summary>{{ $group }}<span>{{ count($groupDefinitions) }}</span></summary>
            @foreach($groupDefinitions->sortBy(fn ($item, $key) => $settings->get($key)?->draft['position'] ?? $item['defaults']['position'] ?? 0) as $sectionKey => $item)
                <a class="section-link {{ $section === $sectionKey ? 'active' : '' }}" href="{{ route('admin.edit', ['section' => $sectionKey]) }}" @if($section === $sectionKey) aria-current="page" @endif><span class="section-dot {{ isset($item['fields']['enabled']) && !($settings->get($sectionKey)?->draft['enabled'] ?? $item['defaults']['enabled']) ? 'off' : '' }}"></span>{{ $item['label'] }}<span class="section-arrow">›</span></a>
            @endforeach</details>
        @endforeach</div><a class="library-shortcut" href="{{ route('admin.media') }}">▧ Abrir biblioteca de imágenes ↗</a>
    </aside>
    <section class="preview-panel" aria-label="Vista previa del borrador"><div class="preview-toolbar"><span><i class="live-dot"></i>Vista previa del borrador</span><div class="device-switch" role="group" aria-label="Tamaño de vista previa"><button type="button" class="active" data-device="desktop">Escritorio</button><button type="button" data-device="mobile">Móvil</button></div><a href="{{ $previewUrl }}" target="_blank" rel="noopener" aria-label="Abrir vista previa">↗</a></div>
        <div class="preview-stage"><div class="preview-canvas"><iframe id="site-preview" title="Vista previa privada del sitio" src="{{ $previewUrl }}" data-section-target="{{ $previewSection }}"></iframe></div></div>
        <div class="preview-bottom"><button type="button" class="text-button" id="select-section">⌖ Seleccionar una sección en la vista</button><span>Guarda el borrador para actualizar esta vista.</span></div>
    </section>
    <aside class="properties-panel"><div class="panel-heading"><span class="eyebrow">{{ $definition['group'] }}</span><h2>{{ $definition['label'] }}</h2><p>Los visitantes verán los cambios cuando publiques.</p></div>
        <form id="section-form" action="{{ route('admin.save', $section) }}" method="POST">@csrf @method('PUT')<input type="hidden" name="revision" value="{{ $revision }}"><div class="properties-fields">
        @foreach($definition['fields'] as $key => $field)
            @if($key === 'custom_background')<details class="design-controls"><summary>Diseño y espaciado</summary>@endif
            @include('admin.field', ['name' => 'data['.$key.']', 'path' => 'data.'.$key, 'value' => $data[$key] ?? null])
            @if($key === 'padding_bottom')</details>@endif
        @endforeach</div><div class="form-bottom"><span class="save-state" id="save-state">Borrador guardado</span><button class="button primary full-width" type="submit">Guardar borrador →</button></div></form>
        <div class="section-actions">@if($definition['page'])<span class="muted">Orden de la sección</span><div class="order-buttons">@foreach(['up' => '↑ Subir', 'down' => '↓ Bajar'] as $direction => $label)<form action="{{ route('admin.reorder', $section) }}" method="POST">@csrf<input type="hidden" name="direction" value="{{ $direction }}"><button type="submit" class="button quiet">{{ $label }}</button></form>@endforeach</div>@endif
        <form action="{{ route('admin.discard', $section) }}" method="POST">@csrf<button type="submit" class="text-button danger">Restaurar última versión publicada</button></form></div>
    </aside>
</main>
<dialog id="media-picker" class="media-dialog"><div class="dialog-heading"><div><span class="eyebrow">TU UNIVERSO VISUAL</span><h2>Elige una fotografía</h2></div><button type="button" data-close-dialog class="icon-button" aria-label="Cerrar biblioteca">×</button></div><input id="media-search" class="search-input" type="search" placeholder="Buscar por nombre…" aria-label="Buscar fotografía"><div class="picker-grid">@foreach($library as $image)<button type="button" class="picker-image" data-image="{{ $image['url'] }}" data-src="{{ asset($image['url']) }}" data-name="{{ $image['name'] }}"><img src="{{ asset($image['url']) }}" alt="{{ $image['name'] }}" loading="lazy"><span>{{ $image['name'] }}<small>{{ $image['provider'] }}</small></span></button>@endforeach</div><div class="dialog-footer"><span>Las fotografías originales también están disponibles.</span><a href="{{ route('admin.media') }}" target="_blank" rel="noopener">Subir una fotografía ↗</a></div></dialog>
@endsection
