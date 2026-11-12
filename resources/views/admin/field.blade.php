@php $value = old($path, $value); @endphp
<div class="editor-field field-{{ $field['type'] }}">
@if($field['type'] === 'boolean')
    <label class="switch-label"><span>{{ $field['label'] }}</span><input type="hidden" name="{{ $name }}" value="0"><input class="switch" type="checkbox" name="{{ $name }}" value="1" @checked($value)></label>
@elseif($field['type'] === 'repeater')
    <div class="repeater" data-next-index="{{ count($value ?? []) }}" data-max="{{ $field['max'] }}" data-min="{{ $field['min'] }}"><div class="field-caption"><strong>{{ $field['label'] }}</strong><small>Arriba y abajo cambian el orden.</small></div><div class="repeater-items">
        @foreach($value ?? [] as $index => $item)
        <details class="repeater-item" @if($loop->first) open @endif><summary class="repeater-heading"><span>{{ $item['names'] ?? $item['title'] ?? $item['label'] ?? 'Elemento '.$loop->iteration }}</span><div><button type="button" data-item-up aria-label="Subir elemento">↑</button><button type="button" data-item-down aria-label="Bajar elemento">↓</button><button type="button" data-remove-item aria-label="Quitar elemento">×</button></div></summary>
        @foreach($field['fields'] as $childKey => $childField)@include('admin.field', ['field' => $childField, 'name' => $name.'['.$index.']['.$childKey.']', 'path' => $path.'.'.$index.'.'.$childKey, 'value' => $item[$childKey] ?? ''])@endforeach
        </details>@endforeach</div>
        <template><details class="repeater-item" open><summary class="repeater-heading"><span>Nuevo elemento</span><div><button type="button" data-item-up aria-label="Subir elemento">↑</button><button type="button" data-item-down aria-label="Bajar elemento">↓</button><button type="button" data-remove-item aria-label="Quitar elemento">×</button></div></summary>@foreach($field['fields'] as $childKey => $childField)@include('admin.field', ['field' => $childField, 'name' => $name.'[__INDEX__]['.$childKey.']', 'path' => $path.'.__INDEX__.'.$childKey, 'value' => $childField['default'] ?? ($childField['type'] === 'select' ? array_key_first($childField['options']) : '')])@endforeach</details></template>
        <button type="button" class="button dashed full-width" data-add-item>＋ Añadir elemento</button>
    </div>
@elseif($field['type'] === 'image')
    <label class="field-caption">{{ $field['label'] }}</label><div class="image-control"><img src="{{ $value ? asset($value) : asset('assets/images/hero/hero-01.webp') }}" alt="Fotografía seleccionada" class="image-thumbnail {{ !$value ? 'placeholder-image' : '' }}"><input type="hidden" name="{{ $name }}" value="{{ $value }}"><div><button type="button" class="button quiet" data-pick-image>Elegir imagen</button><button type="button" class="text-button" data-clear-image>Quitar</button></div></div>
@elseif(in_array($field['type'], ['textarea', 'lines']))
    <label class="field">{{ $field['label'] }}<textarea name="{{ $name }}" rows="{{ $field['type'] === 'lines' ? 6 : 4 }}" maxlength="{{ $field['type'] === 'lines' ? 20000 : 5000 }}">{{ is_array($value) ? implode("\n", $value) : $value }}</textarea></label>
    @if($field['type'] === 'lines')<small class="field-help">Escribe un elemento en cada línea.</small>@endif
@elseif(in_array($field['type'], ['select', 'font']))
    <label class="field">{{ $field['label'] }}<select name="{{ $name }}">@foreach($field['type'] === 'font' ? array_combine(array_keys(config('editor.fonts')), array_keys(config('editor.fonts'))) : $field['options'] as $option => $label)<option value="{{ $option }}" @selected($value === $option)>{{ $label }}</option>@endforeach</select></label>
    @if($field['type'] === 'font')<div class="font-sample" style="font-family:'{{ array_key_exists($value, config('editor.fonts')) ? $value : 'serif' }}',serif">El arte de contar historias.</div>@endif
@else
    <label class="field">{{ $field['label'] }}<input name="{{ $name }}" type="{{ $field['type'] === 'decimal' ? 'number' : (in_array($field['type'], ['color','number','email','url']) ? $field['type'] : ($field['type'] === 'calendly' ? 'url' : 'text')) }}" value="{{ $value }}" @if(in_array($field['type'], ['number','decimal'])) min="{{ $field['min'] }}" max="{{ $field['max'] }}" @endif @if($field['type'] === 'decimal') step="0.01" @endif @if(in_array($field['type'], ['number','decimal','color','font'])) required @endif @if($field['type'] === 'calendly') placeholder="https://calendly.com/tu-estudio/conversemos" @endif></label>
    @if($field['type'] === 'calendly')<small class="field-help">Pega el enlace público de tu evento. La disponibilidad se gestiona directamente en Calendly. Puedes dejarlo vacío mientras creas tu cuenta.</small>@endif
@endif
@error($path)<span class="field-error">{{ $message }}</span>@enderror
</div>
