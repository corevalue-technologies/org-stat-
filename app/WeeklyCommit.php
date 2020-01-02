<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeeklyCommit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'repository_id', 'author_id', 'week', 'additions', 'deletions', 'commits'
    ];
}
