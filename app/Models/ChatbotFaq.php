<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    /** @use HasFactory<\Database\Factories\ChatbotFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
    ];
}
