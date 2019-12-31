<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'fullname', 'html_url',
    ];
}
