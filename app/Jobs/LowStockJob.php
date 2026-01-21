<?php

namespace App\Jobs;

use App\Mail\LowStockMail;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class LowStockJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function handle()
    {
        Mail::to(config('mail.admin_email'))
            ->send(new LowStockMail($this->product));
    }
}
