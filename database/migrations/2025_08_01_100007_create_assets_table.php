<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('location');
            $table->date('purchase_date');
            $table->decimal('value', 10, 2);
            $table->string('condition');
            $table->string('assigned')->nullable(); // Or foreignId('assigned')->nullable()->constrained('employees')
            $table->string('assigned_to_name')->nullable();
            $table->date('assigned_date');
            $table->string('status');
            $table->string('property_no');
            $table->string('serial_no');
            $table->string('serviceable');
            $table->string('unserviceable');
            $table->string('unit_qty');
            $table->string('coa_representative');
            $table->date('coa_date');
            $table->string('image')->nullable();
            $table->date('return_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};