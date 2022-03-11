<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLicense extends Model
{
    use HasFactory;
    protected $table = 'content_license_records';
    protected $primaryKey = 'license_id';
    public $timestamps = false;
}
