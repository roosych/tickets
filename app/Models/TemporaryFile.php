<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryFile extends Model
{
    protected $fillable = [
        'folder', 'size', 'extension',
        'filename', 'unique_filename',
    ];
}
