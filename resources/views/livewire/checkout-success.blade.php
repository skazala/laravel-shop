<div
    wire:init="checkOrder"
    wire:poll.2s="checkOrder"
    class="text-center mt-12"
>
    <h1 class="text-2xl font-semibold mb-4">
        Payment successful 🎉
    </h1>

    <p class="text-gray-600">
        We are processing your order…
    </p>

    <p class="text-sm text-gray-400 mt-4">
        Please don’t close this page.
    </p>
</div>
