<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Book extends Model implements JWTSubject
{
    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'publisher_id',
        'category_id',
        'description',
        'edition',
        'publication_year',
        'language',
        'pages',
        'cover_image',
        'copies',
        'available_copies',
        'location',
        'status'
    ];

    public function author(){
        return $this->belongsTo(Author::class);
    }

    public function publisher(){
        return $this->belongsTo(Publisher::class);
    }

    public function borrowing(){
        return $this->hasMany(borrowing::class);
    }

    public function reserve(){
        return $this->hasMany(Reservation::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
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
