<?php

use App\Console\Commands\DispatchScheduledPostsCommand;
use App\Console\Commands\DispatchScheduledProductsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('social:dispatch-scheduled-posts', function () {
    $this->call(DispatchScheduledPostsCommand::class);
})->purpose('Dispatch scheduled social posts that are due');

Schedule::command('social:dispatch-scheduled-posts')->everyMinute();

Artisan::command('products:dispatch-scheduled', function () {
    $this->call(DispatchScheduledProductsCommand::class);
})->purpose('Publish scheduled product listings whose time has come');

Schedule::command('products:dispatch-scheduled')->everyMinute();
