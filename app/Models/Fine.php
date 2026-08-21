<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $fillable = [
        'borrowing_id',
        'amount',
        'paid',
        'payment_date',
        'remarks'
    ];

    public function borrow(){
        return $this->belongsTo(Borrowing::class);
    }
}
