<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;


class Author extends Model implements JWTSubject
{
    protected $fillable = [
        'name',
        'biography',
        'country',
        'birth_date',
        'photo',
        'status',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

            public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
