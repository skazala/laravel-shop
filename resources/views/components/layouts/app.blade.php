<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Little Yarn Shop</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="antialiased">

    @include('partials.header')

    <main class="p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
