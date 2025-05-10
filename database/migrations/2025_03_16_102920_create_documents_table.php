<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->string('documentID')->primary();
            $table->string('studentID');
            $table->string('courseID');
            $table->string('fileName');
            $table->string('fileFormat');
            $table->integer('fileSize');
            $table->boolean('documentStatus');
            $table->boolean('removeFile');
            $table->timestamps();
        
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('courseID')->references('courseID')->on('courses')->onDelete('cascade'); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_requirements');
    }
};
