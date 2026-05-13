<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Review;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public int $rating = 0;
    public string $comment = '';

    public bool $canReview = false;
    public bool $hasReviewed = false;
    public ?string $reviewSuccess = null;

    public function mount(int $id): void
    {
        $this->product = Cache::remember(
            "product.{$id}",
            now()->addMinutes(5),
            fn () => Product::with(['reviews.user'])->findOrFail($id)
        );

        if (Auth::check()) {
            $this->hasReviewed = Review::where('user_id', Auth::id())
                ->where('product_id', $this->product->id)
                ->exists();

            $this->canReview = !$this->hasReviewed
                && $this->product->wasPurchasedBy(Auth::id());
        }
    }

    public function submitReview(): void
    {
        $this->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        abort_unless(Auth::check(), 401);
        abort_unless($this->product->wasPurchasedBy(Auth::id()), 403);
        abort_unless(!$this->hasReviewed, 403);

        Review::create([
            'user_id'    => Auth::id(),
            'product_id' => $this->product->id,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
        ]);

        $this->product = Product::with(['reviews.user'])->find($this->product->id);
        $this->hasReviewed = true;
        $this->canReview   = false;
        $this->rating      = 0;
        $this->comment     = '';
        $this->reviewSuccess = 'Thank you! Your review has been submitted.';

        Cache::forget("product.{$this->product->id}");
    }

    public function addToCart(): void
    {
        try {
            app(CartService::class)->add($this->product->id, Auth::user());
            $this->dispatch('cart-updated');
            session()->flash('success', 'Product added to cart');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
