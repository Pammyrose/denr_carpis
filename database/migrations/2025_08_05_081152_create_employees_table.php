<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('sex');
            $table->string('email')->unique();
            $table->string('contact_no')->unique();
            $table->string('emp_status');
            

$table->string('position_name', 255)->nullable();
$table->string('assignment_name', 255)->nullable();
$table->string('div_sec_unit', 255)->nullable();


            $table->timestamps();

// NOW add foreign keys (after columns exist)
            $table->foreign('position_name')
                  ->references('name')
                  ->on('positions')
                  ->onDelete('restrict');

            $table->foreign('assignment_name')
                  ->references('name')
                  ->on('assignments') // Make sure this table exists!
                  ->onDelete('restrict');

            $table->foreign('div_sec_unit')
                  ->references('name')
                  ->on('div_sec_units') // Make sure this table exists!
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
