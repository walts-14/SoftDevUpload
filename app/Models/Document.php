<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // 1️⃣ Explicitly set the table name (if Laravel’s pluralization isn’t right):
    protected $table = 'documents';

    // 2️⃣ Allow mass assignment on the columns you’ll be filling:
    protected $fillable = [
        'user_id',           // student’s ID in your table
        'document_type',     // e.g. "Birth Certificate"
        'file_path',         // the storage path
        'status',            // e.g. "Pending"
        'rejection_reason',  // if any
    ];

    // 3️⃣ (Optional) If you need custom casts
    protected $casts = [
        'status' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'studentID');
    }

        public function requirements()
    {
        return $this->hasMany(CourseRequirement::class, 'courseID', 'courseID');
    }


}