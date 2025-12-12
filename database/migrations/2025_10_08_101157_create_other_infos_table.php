<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Links to employees table
            $table->date('date_of_birth')->nullable(); // Required
            $table->string('tin_no')->nullable(); // Optional
            $table->date('date_appointment')->nullable(); // Optional
            $table->date('date_last_promotion')->nullable(); // Optional
            $table->string('civil_service')->nullable(); // Optional
            $table->text('education')->nullable(); // Optional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_infos');
    }
};