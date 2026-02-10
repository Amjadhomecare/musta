<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Model implements JWTSubject
{
    use HasFactory;

    // this is for connecting with Two DB fc and hc
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }


    // Allow mass assignment for all fields (optional: depending on your use case)
    protected $guarded = [];

    // JWT methods implementation

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // This returns the model's primary key (id by default)
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships


    /**
     * Define a relationship to customer attachments.
     */
    public function customerAttachment()
    {
        return $this->hasMany(customerAttach::class, 'customer_id', 'id');
    }

    public function customerP1(){
        return $this->hasMany(categoryOne::class, 'customer_id', 'id');
    }

    public function customerP4(){
        return $this->hasMany(Category4Model::class,'customer_id','id');
    }

    public function userInfo(){
        return $this->belongsTo(User::class , 'created_by','name' );
    }
}
