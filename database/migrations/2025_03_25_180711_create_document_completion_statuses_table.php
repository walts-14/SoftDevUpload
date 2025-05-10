<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_completion_status', function (Blueprint $table) {
            $table->string('statusID')->primary();
            $table->string('documentID');
            $table->string('studentID');
            $table->boolean('isUploaded');
            $table->string('verificationStatus');
            $table->text('comments')->nullable();
            $table->timestamps();
        
            $table->foreign('documentID')->references('documentID')->on('document_requirements')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('document_completion_statuses');
    }
};
