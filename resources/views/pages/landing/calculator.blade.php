<div class="wedding-calculator reveal" data-wedding-calculator>
    <div><h3>{{ $data['calculator_title'] ?? 'Diseña una primera idea de tu experiencia' }}</h3><p>{{ $data['calculator_note'] ?? 'Este importe es orientativo. La propuesta final se confirma directamente con el estudio.' }}</p></div>
    <div class="calculator-fields">
        <label>Cobertura<select data-rate-coverage><option value="">Seleccionar cobertura</option>@foreach($rates->where('category', 'coverage') as $rateIndex => $rate)<option value="{{ $rateIndex }}" data-amount="{{ $rate['amount'] }}">{{ $rate['label'] }}</option>@endforeach</select></label>
        @if($rates->where('category', 'city')->isNotEmpty())<label>Ciudad o destino<select data-rate-city><option value="">Seleccionar destino</option>@foreach($rates->where('category', 'city') as $rateIndex => $rate)<option value="{{ $rateIndex }}" data-amount="{{ $rate['amount'] }}">{{ $rate['label'] }}</option>@endforeach</select></label>@endif
        @if($rates->where('category', 'extra')->isNotEmpty())<fieldset><legend>Opciones adicionales</legend>@foreach($rates->where('category', 'extra') as $rateIndex => $rate)<label class="calculator-extra"><input type="checkbox" value="{{ $rateIndex }}" data-rate-extra data-amount="{{ $rate['amount'] }}"><span>{{ $rate['label'] }}</span><small>+ S/ {{ number_format((float) $rate['amount'], 2, '.', ',') }}</small></label>@endforeach</fieldset>@endif
        <div class="calculator-result"><span>Estimado orientativo</span><output data-calculator-total aria-live="polite">Selecciona la cobertura@if($rates->where('category', 'city')->isNotEmpty()) y el destino@endif</output></div>
    </div>
</div>
