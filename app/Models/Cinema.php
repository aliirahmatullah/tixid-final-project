<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cinema extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'location',
    ];

    //mendefinisikan relasi karna ke schedule itu many jadi jamak (s)
    public function schedules()
    //hasMany ( one to many)
    //hasOne(one to one)
    {
        return $this->hasMany(schedule::class);
    }
}
