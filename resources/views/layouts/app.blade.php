<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Buku Tamu Digital' }}</title>

    <!-- JURUS SAKTI: Mengambil Favicon dari Database Pengaturan -->
    @php
        $pengaturan = \App\Models\Pengaturan::first();
        $favicon =
            $pengaturan && $pengaturan->favicon ? asset('storage/' . $pengaturan->favicon) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ $favicon }}">

</head>

<body>
    {{ $slot }}
</body>

</html>
