<?php

namespace LaravelMonite\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monite extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;


    /**
     * Get the parent addressable model.
     */
    public function moniteable(): MorphTo
    {
        return $this->morphTo();
    }
}
