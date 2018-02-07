<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'code',
        'title',
    ];

    /**
     * Get the answers for the question.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
