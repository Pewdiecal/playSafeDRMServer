<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDetails extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'account_details_records';
    protected $primaryKey = 'account_id';
    protected $fillable = ['registered_region', 
    'max_streaming_quality', 
    'subscribtion_status'];
    protected $attributes = [
        'downloaded_content_qty' => 0,
        'total_streaming_hours' => 0,
        'loggedIn_device_num' => 0
    ];
}
