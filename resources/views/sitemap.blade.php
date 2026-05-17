<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Páginas estáticas --}}
    @foreach($estaticas as $url)
    <url>
        <loc>{{ $url['url'] }}</loc>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
    @endforeach

    {{-- Localidades --}}
    @foreach($localidades as $loc)
    @if(isset($loc['slug']))
    <url>
        <loc>{{ url('/localidades/' . $loc['slug']) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endif
    @endforeach

    {{-- Categorías del blog --}}
    @foreach($categorias as $cat)
    <url>
        <loc>{{ url('/blog/categoria/' . $cat->slug) }}</loc>
        <lastmod>{{ $cat->updated_at->toDateString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Artículos del blog --}}
    @foreach($articulos as $articulo)
    <url>
        <loc>{{ url('/blog/' . $articulo->slug) }}</loc>
        <lastmod>{{ $articulo->updated_at->toDateString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach

</urlset>
