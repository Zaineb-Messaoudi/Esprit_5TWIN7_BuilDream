<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'amount', 'payment_stage', 'due_date', 'payment_date',
        'transaction_reference', 'status', 'payment_method', 'refund_status',
        'refund_attempts', 'refund_target_amount', 'refunded_amount',
        'stripe_payment_intent_id', 'stripe_refund_id', 'refunded_at',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'due_date' => 'date',
        'refunded_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
