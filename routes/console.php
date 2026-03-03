<?php

use App\Jobs\DailySalesReportJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DailySalesReportJob)
    ->dailyAt('20:00');
