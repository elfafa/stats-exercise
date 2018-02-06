<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Percent extends Model
{
    protected $fillable = [
        'file_id',
        'answer_id',
        'segment_id',
        'value',
    ];

    /**
     * Get the file that owns the percent.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the answer that owns the percent.
     */
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    /**
     * Get the segment that owns the percent.
     */
    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }
}
