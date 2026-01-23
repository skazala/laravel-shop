<div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">

    <button @click="open = true" class="px-3 py-1 border rounded hover:bg-gray-100">
        Login
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
        <div @click.outside="open = false" class="bg-white p-6 rounded w-full max-w-sm">
            <h2 class="text-lg font-semibold mb-4">Login</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <input type="email" name="email" placeholder="Email" required
                        class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <input type="password" name="password" placeholder="Password" required
                        class="w-full border px-3 py-2 rounded">
                </div>

                <button type="submit" class="w-full bg-black text-white py-2 rounded">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
