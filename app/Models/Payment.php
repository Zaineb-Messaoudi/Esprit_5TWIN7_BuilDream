<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'amount', 'payment_date',
        'transaction_reference', 'status', 'payment_method',
    ];

    protected $casts = ['payment_date' => 'datetime'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}