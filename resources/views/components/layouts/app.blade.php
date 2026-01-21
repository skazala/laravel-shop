<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shop</title>

    @livewireStyles
</head>
<body class="antialiased">

    <header class="p-4 border-b flex justify-end">
        <livewire:cart-badge />
    </header>

    <main class="p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
