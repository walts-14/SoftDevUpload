<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory;

    protected $table = 'students'; // Ensure correct table name
    protected $primaryKey = 'studentID'; // Define primary key
    public $incrementing = false; // If studentID is not auto-incremented
    protected $keyType = 'string'; // Adjust based on your database

    protected $fillable = ['studentID', 'courseID', 'email', 'password', 'name'];

    protected $hidden = ['password'];

    // Automatically hash password when setting it
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = bcrypt($value);
    }
}

