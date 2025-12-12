<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('div_sec_units', function (Blueprint $table) {
            $table->id();
            $table->string('org_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();


            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('div_sec_units');
    }
};