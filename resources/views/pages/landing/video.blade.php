@php
    $videoId = 'landing-video-'.\Illuminate\Support\Str::uuid();
    $videoDeferred = $deferred ?? false;
    $videoAutoplay = $autoplay ?? false;
    $videoDescription = $videoLabel ?? 'Video del estudio';
@endphp
<div class="landing-video-player" data-media-player>
    <video id="{{ $videoId }}" data-landing-video playsinline preload="none" controlslist="nodownload noremoteplayback nofullscreen" disablepictureinpicture disableremoteplayback @if($videoPoster ?? '') poster="{{ asset($videoPoster) }}" @endif @if($videoDeferred) data-video-src="{{ asset($videoUrl) }}" @else src="{{ asset($videoUrl) }}" @endif @if($videoAutoplay) data-autoplay-video muted loop @endif aria-label="{{ $videoDescription }}"></video>
    <div class="landing-video-controls" data-video-controls>
        <button type="button" class="landing-video-control" data-video-toggle aria-label="Reproducir video" aria-controls="{{ $videoId }}" aria-pressed="false"><span data-video-play-icon>@include('pages.landing.icon', ['name' => 'play'])</span><span data-video-pause-icon hidden>@include('pages.landing.icon', ['name' => 'pause'])</span></button>
        <button type="button" class="landing-video-control" data-video-restart aria-label="Reiniciar video" aria-controls="{{ $videoId }}">@include('pages.landing.icon', ['name' => 'arrow-path'])</button>
    </div>
    <p class="landing-video-status" data-video-status role="status" aria-live="polite" hidden></p>
    <noscript><p class="landing-video-status">Activa JavaScript para reproducir este video.</p></noscript>
</div>
