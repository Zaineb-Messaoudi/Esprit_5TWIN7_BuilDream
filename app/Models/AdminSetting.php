<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin-configurable system settings.
 */
class AdminSetting extends Model
{
    use HasFactory;

    protected $table = 'admin_settings';

    protected $fillable = [
        'key',
        'group',
        'value',
        'type',
        'description',
        'is_public',
        'is_editable',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_editable' => 'boolean',
        ];
    }

    /** Get the typed value. */
    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /** Set the typed value. */
    public function setTypedValue(mixed $value): void
    {
        $this->value = match ($this->type) {
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /** Get a setting by key. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->getTypedValue() : $default;
    }

    /** Set a setting by key. */
    public static function set(string $key, mixed $value, string $type = 'string', ?string $group = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => match ($type) {
                    'integer' => (string) (int) $value,
                    'float' => (string) (float) $value,
                    'boolean' => $value ? 'true' : 'false',
                    'json' => json_encode($value),
                    default => (string) $value,
                },
                'type' => $type,
            ]
        );
    }

    /** Get all settings in a group. */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()])
            ->toArray();
    }

    /** Get all public settings. */
    public static function getPublic(): array
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()])
            ->toArray();
    }

    /** Scope for editable settings. */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /** Scope for settings in a group. */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
