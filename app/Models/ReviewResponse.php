<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Response to a review from the reviewee.
 */
class ReviewResponse extends Model
{
    use HasFactory;

    protected $table = 'review_responses';

    protected $fillable = [
        'review_id',
        'user_id',
        'response',
    ];

    /** The review being responded to. */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /** The user who wrote the response. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
