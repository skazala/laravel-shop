<?php

namespace App\Jobs;

use App\Mail\DailySalesReportMail;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DailySalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $items = OrderItem::with('product')
            ->whereDate('created_at', today())
            ->get();

        Mail::to(config('mail.admin_email'))
            ->send(new DailySalesReportMail($items));
    }
}
