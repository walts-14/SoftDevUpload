<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'courseID'; // optional, if you're not using default 'id'
    public $incrementing = false;       // optional, if courseID is not auto-increment
    protected $keyType = 'string';      // optional, if courseID is a string like 'IT201'

    protected $fillable = [
        'courseID',
        'course_name',
    ];
}
