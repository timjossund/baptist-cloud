<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_follows')->default(true);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('first_published_at')->nullable()->after('published_at');
        });

        DB::table('posts')
            ->whereNotNull('published_at')
            ->update(['first_published_at' => DB::raw('published_at')]);

        Schema::create('follow_digest_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('digest_date');
            $table->timestamps();

            $table->unique(['user_id', 'digest_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_digest_sends');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('first_published_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notify_follows');
        });
    }
};
