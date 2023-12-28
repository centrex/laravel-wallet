<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Traits;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasUuid
{
    protected $uuid_column;

    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            if (!Schema::hasColumn($model->getTable(), $model->getUuidColumnName())) {
                return;
            }

            if (!is_null($model->{$model->getUuidColumnName()})) {
                return;
            }
            $model->{$model->getUuidColumnName()} = Str::uuid()->toString();
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->getUuidColumnName();
    }

    /** Get UUID Column Name. */
    public function getUuidColumnName(): string
    {
        return $this->uuid_column ?? 'uuid';
    }

    /** Scope a query to only include popular users. */
    public function scopeUuid(Builder $query, $value): Builder
    {
        return $query->where($this->getUuidColumnName(), $value);
    }
}
