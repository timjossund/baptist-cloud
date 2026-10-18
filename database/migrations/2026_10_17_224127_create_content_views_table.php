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
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16);
            $table->unsignedBigInteger('item_id');
            $table->date('period');
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'kind', 'item_id', 'period']);
            $table->index(['user_id', 'period', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
