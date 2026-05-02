<?php

namespace App\Console\Commands;

use App\Services\ScheduledPostService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DispatchScheduledPostsCommand extends Command
{
    protected $signature = 'social:dispatch-scheduled-posts';

    protected $description = 'Dispatch due scheduled social posts to the queue';

    public function handle(ScheduledPostService $scheduledPostService): int
    {
        // Fresh environments may start the scheduler before migrations finish.
        if (! Schema::hasTable('scheduled_posts')) {
            $this->warn('Skipping scheduled social post dispatch because the scheduled_posts table is not available yet.');

            return self::SUCCESS;
        }

        $count = $scheduledPostService->dispatchDue();

        $this->info("Dispatched {$count} scheduled social post(s).");

        return self::SUCCESS;
    }
}
