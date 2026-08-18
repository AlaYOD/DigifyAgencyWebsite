<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            // The key is immutable once created; use it as the stable integration identifier.
            $table->string('key')->unique();
            $table->jsonb('name');
            $table->string('color', 7);
            $table->smallInteger('sort_order');
            $table->boolean('is_default');
            $table->boolean('is_terminal');
            $table->enum('outcome', ['positive', 'negative'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
