@extends('layouts.app')

@section('title', $site->get('portfolio_page_header.page_title'))

@section('content')
@foreach($site->pageSections('portfolio') as $sectionKey)
    @include('pages.sections.'.$sectionKey)
@endforeach
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Vista ampliada" aria-hidden="true">
    <button class="lightbox-close" type="button" aria-label="Cerrar">×</button>
    <button class="lightbox-prev" type="button" aria-label="Fotografía anterior">❮</button>
    <div class="lightbox-content">
        <img class="lightbox-image" src="" alt="">
        <p class="lightbox-caption"></p>
    </div>
    <button class="lightbox-next" type="button" aria-label="Fotografía siguiente">❯</button>
</div>
@endsection
