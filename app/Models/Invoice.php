<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'invoice_number', 'issue_date',
        'subtotal', 'tax', 'total', 'status', 'balance_reminder_sent_at',
        'balance_reminder_three_days_sent_at', 'balance_reminder_due_today_sent_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'balance_reminder_sent_at' => 'datetime',
        'balance_reminder_three_days_sent_at' => 'datetime',
        'balance_reminder_due_today_sent_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
