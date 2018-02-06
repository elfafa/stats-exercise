<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'question_id',
        'title',
    ];

    /**
     * Get the question that owns the answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The areas that belong to the answer.
     */
    public function areas()
    {
        return $this->belongsToMany(Area::class)->withTimestamps();
    }

    /**
     * The categories that belong to the answer.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
}
