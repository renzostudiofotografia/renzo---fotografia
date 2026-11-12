@extends('layouts.app')

@section('title', $site->get('navigation.booking'))

@section('content')
@if($site->get('booking.enabled') || $site->previewSection() === 'booking')
<section class="booking-section" id="citas" data-editor-section="booking">
    @include('components.site.booking')
</section>
@else
<section class="booking-section"><div class="booking-container"><h1>Conversemos</h1><p>Para coordinar una cita, visita nuestra página de contacto.</p><a class="cta-btn primary-btn" href="{{ route('contact') }}">Contacto</a></div></section>
@endif
@endsection
