<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticleVersion extends Model
{
    protected $guarded = [];

    public function article()
    {
        return $this->belongsTo(KbArticle::class, 'kb_article_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
