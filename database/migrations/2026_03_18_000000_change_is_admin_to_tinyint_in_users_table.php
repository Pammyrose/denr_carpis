<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve existing values (0 = user, 1 = super admin, 2 = admin)
        // before was boolean (0/1); now becomes tinyint (0/1/2)
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('is_admin')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->change();
        });
    }
};
