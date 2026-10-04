@extends('layouts.app')

@section('title', $service['title'])

@section('content')
<section class="service-detail-hero">
    <div class="service-detail-hero-container">
        <p class="section-tag">{{ $service['tag'] }}</p>
        <h1 class="service-detail-title">{{ $service['title'] }}</h1>
        <p class="service-detail-subtitle">{{ $service['hero'] }}</p>
    </div>
</section>

<section class="service-detail-body">
    <div class="service-detail-container">
        <div class="service-main-grid">
            <div class="service-main-text">
                <h2 class="service-section-title">{{ $service['intro_title'] }}</h2>
                @foreach($service['paragraphs'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
            <div class="service-main-image"><img src="{{ asset($service['image']) }}" alt="{{ $service['image_alt'] }}"></div>
        </div>

        <div class="service-includes-box">
            <h2 class="includes-title">{{ $service['includes_title'] }}</h2>
            <ul class="includes-list">
                @foreach($service['includes'] as $item)
                    <li><strong>{{ $item['label'] }}:</strong> {{ $item['text'] }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="portfolio-cta">
    <div class="portfolio-cta-container">
        <h2 class="portfolio-cta-title">{{ $service['cta_title'] }}</h2>
        <p class="portfolio-cta-desc">{{ $service['cta_text'] }}</p>
        <a class="cta-btn primary-btn" href="{{ route('contact') }}">{{ $service['cta_label'] }}</a>
    </div>
</section>
@endsection
