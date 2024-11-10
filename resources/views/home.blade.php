<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Title -->
    <title>{{ config('app.name') }}</title>
</head>
<body>
<div id="page" class="p-5">
    <div class="flex gap-3 flex-col">
        @foreach($files as $file)
            <div class="bg-white border-2 border-black rounded p-5 flex flex-col">
                <span class="font-semibold">Postcode - Straatnaam - Plaatsnaam</span>
                <span class="font-light italic">{{ $file->created_at->translatedFormat('F Y') }}</span>

                <a href="{{ route('files.download', $file) }}" class="mt-5 font-bold self-start underline">Downloaden</a>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
