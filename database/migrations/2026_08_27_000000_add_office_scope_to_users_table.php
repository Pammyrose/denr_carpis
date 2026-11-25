<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'office_scope')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('office_scope')->nullable()->after('is_admin')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'office_scope')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('office_scope');
        });
    }
};
