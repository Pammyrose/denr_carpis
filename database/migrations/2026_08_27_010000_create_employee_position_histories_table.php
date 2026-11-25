<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_position_histories', function (Blueprint $table) {
            $table->id();
            // Snapshot fields intentionally remain after an employee is archived/deleted.
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('employee_email')->nullable()->index();
            $table->string('employee_name');
            $table->unsignedBigInteger('position_id')->nullable()->index();
            $table->string('item_code')->nullable();
            $table->string('position_name')->nullable();
            $table->string('assignment_name')->nullable();
            $table->string('div_sec_unit')->nullable();
            $table->string('event', 30)->index();
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['employee_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_position_histories');
    }
};
