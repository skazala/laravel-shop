<?php

namespace App\Livewire;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public ?string $category = null;

    protected ProductRepositoryInterface $productRepo;

    protected $listeners = [
        'cart-updated' => '$refresh',
    ];

    public function boot(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function mount()
    {
        $this->category = request()->query('category');
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function addToCart(int $productId): void
    {
        try {
            app(CartService::class)->add($productId, Auth::user());

            $this->dispatch('cart-updated');

            session()->flash('success', 'Product added to cart');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(CartService $cartService)
    {
        $query = Product::query()
            ->when($this->category, fn ($q) => $q->whereHas('category', fn ($q2) => $q2->where('slug', $this->category)));

        $products = $this->productRepo->paginateByCategory($this->category);

        $quantitiesInCart = $cartService->quantitiesByProductId();

        $products->getCollection()->transform(function (Product $product) use ($quantitiesInCart) {
            $inCart = $quantitiesInCart[$product->id] ?? 0;

            $product->in_cart = $inCart;
            $product->available_quantity = max(
                0,
                $product->stock_quantity - $inCart
            );

            return $product;
        });

        return view('livewire.products', compact('products'));
    }
}
