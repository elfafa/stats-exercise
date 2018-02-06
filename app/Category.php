<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'title',
    ];

    /**
     * The answers that belong to the category.
     */
    public function answers()
    {
        return $this->belongsToMany(Answer::class)->withTimestamps();
    }
}
