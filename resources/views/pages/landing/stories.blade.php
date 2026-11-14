<div class="wedding-stories">
    @foreach($stories as $storyIndex => $story)
        @php $storyDialogId = 'story-dialog-'.$sectionIndex.'-'.$storyIndex; @endphp
        <article class="wedding-story reveal" data-story-index="{{ $storyIndex }}">
            @if($hasVideo($story))
                <div class="story-video-cover">
                    @include('pages.landing.video', ['videoUrl' => $story['video_url'], 'videoPoster' => $story['video_poster'] ?: $story['image'], 'videoLabel' => $story['alt'] ?: $story['names'], 'autoplay' => false, 'deferred' => false])
                    <button class="story-video-action landing-text-link" type="button" data-story-open="{{ $storyIndex }}" aria-controls="{{ $storyDialogId }}" aria-haspopup="dialog">Ver historia @include('pages.landing.icon', ['name' => 'arrow-up-right'])</button>
                </div>
            @else
                <button class="story-cover" type="button" data-story-open="{{ $storyIndex }}" aria-controls="{{ $storyDialogId }}" aria-haspopup="dialog">
                    <img src="{{ asset($story['image']) }}" alt="{{ $story['alt'] }}" loading="lazy" decoding="async" width="1100" height="1400">
                    <span class="story-open-label">Ver historia @include('pages.landing.icon', ['name' => 'arrow-up-right'])</span>
                </button>
            @endif
            <div class="story-caption"><div><p class="story-location">{{ $story['location'] }}@if($story['date']) · {{ $story['date'] }}@endif</p><h3>{{ $story['names'] ?: $story['title'] }}</h3>@if($story['names'] && $story['title'])<p>{{ $story['title'] }}</p>@endif</div><span class="story-index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></div>
            @if($story['description'])<p class="story-description">{{ $story['description'] }}</p>@endif
            @if(!$hasVideo($story))<noscript><a class="landing-text-link" href="{{ asset($story['image']) }}">Abrir fotografía de {{ $story['names'] ?: $story['title'] }} @include('pages.landing.icon', ['name' => 'arrow-up-right'])</a></noscript>@endif
            <dialog class="story-dialog" id="{{ $storyDialogId }}" data-story-dialog aria-labelledby="{{ $storyDialogId }}-title">
                <div class="story-dialog-bar"><span>{{ $data['brand_name'] }}</span><button type="button" class="story-dialog-close" data-story-close aria-label="Cerrar historia">Cerrar @include('pages.landing.icon', ['name' => 'x-mark'])</button></div>
                <header class="story-dialog-intro"><p class="landing-eyebrow">{{ $story['location'] }}@if($story['date']) · {{ $story['date'] }}@endif</p><h2 id="{{ $storyDialogId }}-title">{{ $story['names'] ?: $story['title'] }}</h2>@if($story['names'] && $story['title'])<p class="story-dialog-subtitle">{{ $story['title'] }}</p>@endif @if($story['description'])<p>{{ $story['description'] }}</p>@endif</header>
                <div class="story-dialog-gallery" data-story-gallery></div>
                <template data-story-template>
                    @if($hasVideo($story))
                        <figure>@include('pages.landing.video', ['videoUrl' => $story['video_url'], 'videoPoster' => $story['video_poster'] ?: $story['image'], 'videoLabel' => $story['alt'] ?: $story['names'], 'autoplay' => false, 'deferred' => true])</figure>
                    @else
                        <figure><img data-src="{{ asset($story['image']) }}" alt="{{ $story['alt'] }}" width="1500" height="1100" decoding="async"></figure>
                    @endif
                    @foreach($story['images'] as $storyImage)
                        @if($hasVideo($storyImage))
                            <figure>@include('pages.landing.video', ['videoUrl' => $storyImage['video_url'], 'videoPoster' => $storyImage['video_poster'] ?: $storyImage['image'], 'videoLabel' => $storyImage['alt'] ?: 'Video de la historia', 'autoplay' => false, 'deferred' => true])@if($storyImage['alt'])<figcaption>{{ $storyImage['alt'] }}</figcaption>@endif</figure>
                        @elseif($storyImage['image'] && $storyImage['image'] !== $story['image'])
                            <figure><img data-src="{{ asset($storyImage['image']) }}" alt="{{ $storyImage['alt'] }}" loading="lazy" decoding="async" width="1200" height="1500">@if($storyImage['alt'])<figcaption>{{ $storyImage['alt'] }}</figcaption>@endif</figure>
                        @endif
                    @endforeach
                </template>
                @if($hasPrimaryDestination)<div class="story-dialog-end"><a class="landing-button" href="{{ $primaryButtonUrl }}" data-story-inquire>{{ $data['button_label'] }} @include('pages.landing.icon', ['name' => $buttonIcon])</a></div>@endif
            </dialog>
        </article>
    @endforeach
</div>
