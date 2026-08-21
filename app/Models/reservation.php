<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Reservation extends Model implements JWTSubject
{
    protected $fillable =[
        'member_id',
        'book_id',
        'reservation_date',
        'status'
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }

        public function book()
    {
        return $this->belongsTo(Book::class);
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
