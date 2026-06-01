<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['event', 'description', 'level', 'causer'];

    /**
     * Record a system activity entry.
     */
    public static function record(string $event, string $description, string $level = 'info', ?string $causer = null): void
    {
        static::create([
            'event' => $event,
            'description' => $description,
            'level' => $level,
            'causer' => $causer,
        ]);
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public static function recent(int $limit = 12): Collection
    {
        return static::query()->latest('id')->limit($limit)->get();
    }
}
