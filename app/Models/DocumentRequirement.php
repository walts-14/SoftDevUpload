<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    use HasFactory;

    protected $table = 'document_requirements';
    protected $primaryKey = 'documentID';
    public $incrementing = false; // Since documentID is a string

    protected $fillable = [
    'documentID',
    'studentID',
    'courseID',
    'document_type',
    'fileName',
    'file_path',
    'fileFormat',
    'fileSize',
    'documentStatus',
    'removeFile',
];


    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseID', 'courseID');
    }

    
}