<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{

    //create shell : php artisan make:model UserLog -m
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];
}
