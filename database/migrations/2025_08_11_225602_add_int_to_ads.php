<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bc_ads', function (Blueprint $table) {
            $table->integer('int')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_ads', function (Blueprint $table) {
            $table->dropColumn('int');
        });
    }
};
