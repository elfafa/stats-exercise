<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'title',
    ];

    /**
     * The answers that belong to the area.
     */
    public function answers()
    {
        return $this->belongsToMany(Answer::class)->withTimestamps();
    }
}
