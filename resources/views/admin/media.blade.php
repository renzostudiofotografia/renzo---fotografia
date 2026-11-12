@extends('admin.layout')
@section('title', 'Biblioteca de imágenes')
@section('content')
<main class="page-padding">
    <div class="page-intro">
        <div><span class="eyebrow">TU UNIVERSO VISUAL</span><h1>Imágenes que cuentan historias.</h1><p>Un solo lugar para tus fotografías. Elígelas después desde cualquier sección del editor.</p></div>
        @if($r2Ready)
            <span class="connection-pill connected">● Cloudflare R2 configurado</span>
        @elseif($cloudflareReady)
            <span class="connection-pill connected">● Cloudflare Images configurado</span>
        @else
            <span class="connection-pill">○ Almacenamiento local activo</span>
        @endif
    </div>
    <form class="upload-card" action="{{ route('admin.upload') }}" method="POST" enctype="multipart/form-data">@csrf<div class="upload-icon">↑</div><div><h2>Añade una nueva fotografía</h2><p>JPG, PNG o WebP · hasta 12 MB · máximo 10 000 px por lado</p><input type="file" name="image" accept="image/jpeg,image/png,image/webp" required aria-label="Seleccionar fotografía"><label class="field compact">Descripción de la imagen<input name="alt" maxlength="200" placeholder="Ej. Pareja al atardecer en el Valle Sagrado"></label></div><button class="button primary" type="submit">Subir a mi biblioteca →</button></form>
    @unless($r2Ready || $cloudflareReady)<div class="inline-note">Tus fotografías ya se pueden utilizar en el sitio. Cuando conectes Cloudflare, podrás enviarlas desde sus tarjetas sin cambiar los enlaces.</div>@endunless
    <div class="section-heading"><h2>Tu biblioteca <span class="count-badge">{{ $mediaCount }}</span></h2><a class="text-link" href="{{ route('admin.edit', 'home_hero') }}">Usar imágenes en el editor →</a></div>
    @if($assets->isEmpty())
        <div class="empty-card"><span>▧</span><h3>Tu próxima historia empieza con una imagen.</h3><p>Sube una fotografía para incorporarla a cualquier página.</p></div>
    @else
        <div class="media-grid">
            @foreach($assets as $asset)
                <article class="media-card">
                    <img src="{{ route('media.show', $asset) }}" alt="{{ $asset->alt }}" loading="lazy">
                    <div class="media-card-body">
                        <span class="asset-provider">{{ match ($asset->provider) {
                            'r2' => 'CLOUDFLARE R2',
                            'cloudflare' => 'CLOUDFLARE',
                            default => 'LOCAL',
                        } }} · {{ number_format($asset->size / 1024) }} KB</span>
                        <form action="{{ route('admin.media.update', $asset) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="field compact">Nombre<input name="name" value="{{ $asset->name }}" required maxlength="200"></label>
                            <label class="field compact">Descripción accesible<input name="alt" value="{{ $asset->alt }}" maxlength="200"></label>
                            <button class="button quiet full-width" type="submit">Guardar datos</button>
                        </form>
                        @if($asset->provider === 'local' && $r2Ready)
                            <form action="{{ route('admin.media.r2', $asset) }}" method="POST">
                                @csrf
                                <button class="text-button" type="submit">Enviar a R2 ↗</button>
                            </form>
                        @elseif($asset->provider === 'local' && $cloudflareReady)
                            <form action="{{ route('admin.media.cloudflare', $asset) }}" method="POST">
                                @csrf
                                <button class="text-button" type="submit">Enviar a Cloudflare ↗</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
    <div class="section-heading"><div><h2>Fotografías originales del sitio</h2><p class="muted">También puedes seleccionarlas en el editor. Se conservan en su ubicación original.</p></div></div><div class="original-grid">@foreach(collect($library)->where('provider', 'Original') as $image)<figure><img src="{{ asset($image['url']) }}" alt="{{ $image['name'] }}" loading="lazy"><figcaption>{{ $image['name'] }}</figcaption></figure>@endforeach</div>
</main>
@endsection
