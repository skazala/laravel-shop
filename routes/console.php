<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\DailySalesReportJob;


Schedule::job(new DailySalesReportJob)
    ->dailyAt('20:00');

