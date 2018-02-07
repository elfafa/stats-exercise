<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    const STATUS_INPROGRESS = 'in_progress';
    const STATUS_IMPORTED   = 'imported';

    /**
     * The percents that belong to the file.
     */
    public function percents()
    {
        return $this->hasMany(Percent::class);
    }
}
