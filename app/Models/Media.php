<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'folder',
        'filename',
        'unique_filename',
        'size',
        'extension',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
