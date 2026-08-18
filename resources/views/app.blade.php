<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Digify') }}</title>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @inertiaHead
        @isset($careerJsonLd)
            <script type="application/ld+json">@json($careerJsonLd)</script>
        @endisset
    </head>
    <body class="bg-white text-brand-navy antialiased">
        @inertia
    </body>
</html>
