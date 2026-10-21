<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'equipment_id', 'user_id',
        'start_date', 'end_date', 'total_amount', 'status',
        'cancellation_status', 'cancellation_reason', 'cancellation_requested_at',
        'cancellation_reviewed_by', 'cancellation_reviewed_at',
        'cancellation_refund_percentage', 'cancellation_refund_total',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cancellation_requested_at' => 'datetime',
        'cancellation_reviewed_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function rental()
    {
        return $this->hasOne(Rental::class);
    }

    public function cancellationReviewer()
    {
        return $this->belongsTo(User::class, 'cancellation_reviewed_by');
    }
}
