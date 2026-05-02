<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(array $payload): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $payload['user_id'] ?? null,
            'event' => $payload['event'],
            'method' => $payload['method'],
            'path' => $payload['path'],
            'ip_address' => $payload['ip_address'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null,
            'response_status' => $payload['response_status'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
            'occurred_at' => now(),
        ]);
    }
}
