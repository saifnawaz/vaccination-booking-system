<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes the admin activity log: who approved, rejected, cancelled or deleted what.
 *
 * The causer defaults to the signed-in user. Pass `system: true` for actions taken
 * by scheduled jobs, which have no user.
 */
final class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties  extra details, e.g. a reason or counts
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $causer = null,
        bool $system = false,
    ): ActivityLog {
        $entry = new ActivityLog;
        $entry->action = $action;
        $entry->description = mb_substr($description, 0, 500);
        $entry->causer_id = $system ? null : ($causer ?? auth()->user())?->id;
        $entry->properties = $properties ?: null;

        if ($subject) {
            $entry->subject()->associate($subject);
        }

        $entry->save();

        return $entry;
    }
}
