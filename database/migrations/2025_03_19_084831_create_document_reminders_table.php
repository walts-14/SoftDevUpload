<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('document_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('missing_documents');
            $table->dateTime('reminder_date');
            $table->timestamps();
        });
    }    
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_reminders');
    }
};
