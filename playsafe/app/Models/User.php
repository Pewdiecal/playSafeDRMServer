<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    
    public $timestamps = false;
    protected $table = 'user_records';
    protected $primaryKey = 'user_id';
    protected $fillable = ['username', 'email', 'password', 'account_id', 'is_content_provider'];
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    

    public function getAuthPassword()
    {
        return $this->password; //change the field you want 
    }
}
