<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class WishlistService
{
    public function toggle(User $user, int $productId): bool
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        Wishlist::create([
            'user_id'    => $user->id,
            'product_id' => $productId,
        ]);

        return true;
    }

    public function isWishlisted(User $user, int $productId): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }

    public function wishlistedProductIds(User $user): Collection
    {
        return $user->wishlists()->pluck('product_id');
    }

    public function getForUser(User $user): Collection
    {
        return $user->wishlists()
            ->with('product.category')
            ->get()
            ->map(fn ($w) => $w->product);
    }
}
