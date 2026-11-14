{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($landings as $landing)
    <url><loc>{{ route('landing.show', $landing->slug) }}</loc><lastmod>{{ $landing->updated_at->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
