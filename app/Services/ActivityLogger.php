<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Context;

class ActivityLogger
{
    public function log(
        string $action,
        ?Authenticatable $actor = null,
        ?string $description = null,
        array $meta = []
    ): void {
        $actor = $actor ?? auth('admin')->user() ?? auth('web')->user();

        if (! $actor) {
            return;
        }

        // Use Laravel 13 Context to store and retrieve metadata globally for this request
        Context::add('actor_id', $actor->getAuthIdentifier());
        Context::add('actor_type', class_basename($actor));
        Context::add('last_action', $action);

        ActivityLog::query()->create([
            'actor_type' => class_basename($actor),
            'actor_id' => $actor->getAuthIdentifier(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'meta' => array_merge($meta, Context::all()),
        ]);
    }
}
