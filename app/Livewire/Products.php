<?php

namespace App\Livewire;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTO\ProductViewDTO;
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
            $user = Auth::user();

            app(CartService::class)->add($productId, $user);

            $this->dispatch('cart-updated');

            session()->flash('success', 'Product added to cart');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(CartService $cartService): \Illuminate\View\View
    {
        $paginated = $this->productRepo->paginateByCategory(
            $this->category,
            10,
            $this->getPage()
        );

        $quantities = $cartService->quantitiesByProductId();

        $paginated->getCollection()->transform(
            fn (Product $p) => new ProductViewDTO(
                $p,
                $quantities[$p->id] ?? 0,
                max(0, $p->stock_quantity - ($quantities[$p->id] ?? 0)),
            )
        );

        return view('livewire.products', [
            'products' => $paginated
        ]);
    }
}
