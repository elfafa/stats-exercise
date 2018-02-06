<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $fillable = [
        'type', // total/gender/age/segment
        'sub_type', // for segment only: music/mobile/video/.../radio
        'title', // total/male/female/16-24/25-34/.../55+/music subscribers/music streamers/.../CFRB
    ];

    const TYPE_TOTAL   = 'total';
    const TYPE_GENDER  = 'gender';
    const TYPE_AGE     = 'age';
    const TYPE_SEGMENT = 'segment';
}
