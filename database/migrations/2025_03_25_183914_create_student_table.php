<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('studentID')->primary();
            $table->string('courseID');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->timestamps();
        
            $table->foreign('courseID')->references('courseID')->on('courses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
