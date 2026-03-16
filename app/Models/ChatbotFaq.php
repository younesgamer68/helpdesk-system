<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFaq extends Model
{
    /** @use HasFactory<\Database\Factories\ChatbotFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'question',
        'answer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
