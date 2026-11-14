@php
    $value = old($path, $value);
    if ($field['type'] === 'repeater' && !is_array($value)) {
        $value = [];
    }
    $itemToken = '__ITEM_'.substr(sha1($name), 0, 12).'__';
@endphp
@if(!empty($field['admin_hidden']) && request()->routeIs('admin.landings.*'))
    <input type="hidden" name="{{ $name }}" value="{{ $field['type'] === 'boolean' ? (int) ($field['default'] ?? false) : ($field['default'] ?? $value) }}">
@else
<div class="editor-field field-{{ $field['type'] }}" data-field-name="{{ $name }}" @if(!empty($field['visible_for_sections'])) data-visible-for-sections="{{ implode(',', $field['visible_for_sections']) }}" @endif>
@if($field['type'] === 'boolean')
    <label class="switch-label"><span>{{ $field['label'] }}</span><input type="hidden" name="{{ $name }}" value="0"><input class="switch" type="checkbox" name="{{ $name }}" value="1" @checked($value)></label>
@elseif($field['type'] === 'repeater')
    <div class="repeater" data-next-index="{{ $value ? max(array_map('intval', array_keys($value))) + 1 : 0 }}" data-item-token="{{ $itemToken }}" data-max="{{ $field['max'] }}" data-min="{{ $field['min'] }}"><div class="field-caption"><strong>{{ $field['label'] }}</strong><small>Ordena con las flechas{{ request()->routeIs('admin.landings.*') ? ' o arrastra desde ⋮⋮' : '' }}.</small></div><div class="repeater-items">
        @foreach($value ?? [] as $index => $item)
        <details class="repeater-item" @if($loop->first) open @endif><summary class="repeater-heading"><span data-item-label>{{ $item['names'] ?? $item['title'] ?? $item['name'] ?? $item['label'] ?? $item['question'] ?? 'Elemento '.$loop->iteration }}</span><div>@if(request()->routeIs('admin.landings.*'))<button type="button" draggable="true" data-item-drag aria-label="Arrastrar elemento para reordenar" title="Arrastrar para reordenar">⋮⋮</button>@endif<button type="button" data-item-up aria-label="Subir elemento">↑</button><button type="button" data-item-down aria-label="Bajar elemento">↓</button><button type="button" data-remove-item aria-label="Quitar elemento">×</button></div></summary>
        @foreach($field['fields'] as $childKey => $childField)@include('admin.field', ['field' => $childField, 'name' => $name.'['.$index.']['.$childKey.']', 'path' => $path.'.'.$index.'.'.$childKey, 'value' => $item[$childKey] ?? ''])@endforeach
        </details>@endforeach</div>
        <template><details class="repeater-item" open><summary class="repeater-heading"><span data-item-label>Nuevo elemento</span><div>@if(request()->routeIs('admin.landings.*'))<button type="button" draggable="true" data-item-drag aria-label="Arrastrar elemento para reordenar" title="Arrastrar para reordenar">⋮⋮</button>@endif<button type="button" data-item-up aria-label="Subir elemento">↑</button><button type="button" data-item-down aria-label="Bajar elemento">↓</button><button type="button" data-remove-item aria-label="Quitar elemento">×</button></div></summary>@foreach($field['fields'] as $childKey => $childField)@include('admin.field', ['field' => $childField, 'name' => $name.'['.$itemToken.']['.$childKey.']', 'path' => $path.'.'.$itemToken.'.'.$childKey, 'value' => $childField['default'] ?? match ($childField['type']) { 'repeater' => [], 'boolean' => false, 'select' => array_key_first($childField['options']), 'number', 'decimal' => $childField['min'], default => '' }])@endforeach</details></template>
        <button type="button" class="button dashed full-width" data-add-item>＋ Añadir elemento</button>
    </div>
@elseif($field['type'] === 'image')
    <label class="field-caption">{{ $field['label'] }}</label><div class="image-control"><img src="{{ $value ? asset($value) : asset('assets/images/hero/hero-01.webp') }}" alt="Fotografía seleccionada" class="image-thumbnail {{ !$value ? 'placeholder-image' : '' }}"><input type="hidden" name="{{ $name }}" value="{{ $value }}"><div><button type="button" class="button quiet" data-pick-image>Elegir imagen</button><button type="button" class="text-button" data-clear-image>Quitar</button></div></div>
@elseif($field['type'] === 'video')
    <div class="landing-video-control"><label class="field">{{ $field['label'] }}<input name="{{ $name }}" type="text" inputmode="url" value="{{ $value }}" maxlength="1000" placeholder="https://tu-dominio.com/video.mp4 o /media/123" data-video-url-input></label>@if(request()->routeIs('admin.landings.*'))<div class="landing-video-field-actions"><button type="button" class="button quiet" data-pick-video>Elegir o subir video</button><button type="button" class="text-button" data-clear-video>Quitar video</button></div>@endif</div>
@elseif(in_array($field['type'], ['textarea', 'lines']))
    <label class="field">{{ $field['label'] }}<textarea name="{{ $name }}" rows="{{ $field['rows'] ?? ($field['type'] === 'lines' ? 6 : 4) }}" maxlength="{{ $field['max_length'] ?? ($field['type'] === 'lines' ? 20000 : 5000) }}">{{ is_array($value) ? implode("\n", $value) : $value }}</textarea></label>
    @if($field['type'] === 'lines')<small class="field-help">Escribe un elemento en cada línea.</small>@endif
@elseif(in_array($field['type'], ['select', 'font']))
    <label class="field">{{ $field['label'] }}<select name="{{ $name }}">@foreach($field['type'] === 'font' ? array_combine(array_keys(config('editor.fonts')), array_keys(config('editor.fonts'))) : $field['options'] as $option => $label)<option value="{{ $option }}" @selected($value === $option)>{{ $label }}</option>@endforeach</select></label>
    @if($field['type'] === 'font')<div class="font-sample" style="font-family:'{{ array_key_exists($value, config('editor.fonts')) ? $value : 'serif' }}',serif">El arte de contar historias.</div>@endif
@else
    <label class="field">{{ $field['label'] }}<input name="{{ $name }}" type="{{ $field['type'] === 'decimal' ? 'number' : (in_array($field['type'], ['color','number','email','url']) ? $field['type'] : ($field['type'] === 'calendly' ? 'url' : 'text')) }}" value="{{ $value }}" @if(in_array($field['type'], ['number','decimal'])) min="{{ $field['min'] }}" max="{{ $field['max'] }}" @endif @if($field['type'] === 'decimal') step="0.01" @endif @if(in_array($field['type'], ['number','decimal','color','font'])) required @endif @if($field['type'] === 'calendly') placeholder="https://calendly.com/tu-estudio/conversemos" @endif></label>
    @if($field['type'] === 'calendly')<small class="field-help">Pega el enlace público de tu evento. La disponibilidad se gestiona directamente en Calendly. Puedes dejarlo vacío mientras creas tu cuenta.</small>@endif
@endif
@if(!empty($field['help']))<small class="field-help">{{ $field['help'] }}</small>@endif
@error($path)<span class="field-error">{{ $message }}</span>@enderror
</div>
@endif
