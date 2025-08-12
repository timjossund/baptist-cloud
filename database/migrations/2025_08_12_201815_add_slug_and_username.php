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
        Schema::table('reportings', function (Blueprint $table) {
            $table->string('post_slug');
            $table->string('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportings', function (Blueprint $table) {
            $table->dropColumn('post_slug');
            $table->dropColumn('username');
        });
    }
};
