<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Exceptions\InsufficientStockException;
use App\Jobs\LowStockJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function __construct(
        private PaymentGateway $paymentGateway
    ) {}

    public function startStripeCheckout(User $user): ?string
    {
        $cart = $user->cart()->with('items.product')->firstOrFail();

        if ($cart->items->isEmpty()) {
            Log::critical('Attempted checkout with empty cart', [
                'user_id' => $user->id,
            ]);

            return null;
        }

        $lineItems = $cart->items->map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product->name,
                    ],
                    'unit_amount' => (int) ($item->product->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        return $this->paymentGateway->createCheckoutSession(
            $user,
            $lineItems,
            route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            route('cart')
        );
    }

    public function finalizePaidOrder(array $data): void
    {
        if (Order::where('stripe_session_id', $data['stripe_session_id'])->exists()) {
            return;
        }

        $userId = (int) $data['user_id'];
        $user = User::findOrFail($userId);

        DB::transaction(function () use ($user, $data) {
            $cart = $user->cart()
                ->with('items.product')
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                return;
            }

            $total = 0;
            $lockedProducts = [];

            foreach ($cart->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($item->quantity > $product->stock_quantity) {
                    throw new InsufficientStockException(
                        "Not enough stock for {$product->name}"
                    );
                }

                $total += $item->quantity * $product->price;
                $lockedProducts[$product->id] = $product;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => OrderStatus::Paid,
                'stripe_session_id' => $data['stripe_session_id'],
                'stripe_payment_intent_id' => $data['payment_intent'],
                'stripe_amount_total' => $data['amount_total'],
                'currency' => $data['currency'],
            ]);

            foreach ($cart->items as $item) {
                $product = $lockedProducts[$item->product_id];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ]);

                $product->decrement('stock_quantity', $item->quantity);

                if ($product->stock_quantity < self::LOW_STOCK_THRESHOLD) {
                    LowStockJob::dispatch($product)->afterCommit();
                }
            }

            $cart->items()->delete();
        });
    }
}
