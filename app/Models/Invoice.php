<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'invoice_number', 'issue_date',
        'subtotal', 'tax', 'total', 'status',
    ];

    protected $casts = ['issue_date' => 'date'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}