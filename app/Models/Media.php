<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
