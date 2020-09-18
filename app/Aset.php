<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama', 'quantity', 'brand', 'desc', 'user_id',
    ];
}
