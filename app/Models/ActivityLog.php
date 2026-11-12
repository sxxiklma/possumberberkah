<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'role',
        'action',
        'module',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $module, string $description, ?array $properties = null): self
    {
        $user = auth()->user();
        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'role' => $user?->roles->first()?->name ?? 'Guest',
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}

