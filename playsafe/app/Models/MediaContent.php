<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaContent extends Model
{
    use HasFactory;

    protected $table = 'media_content_records';
    protected $primaryKey = 'content_id';
    public $timestamps = false;
    protected $guarded = [];
}
