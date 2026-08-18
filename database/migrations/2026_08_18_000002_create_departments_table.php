<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->jsonb('slug');
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        DB::statement('CREATE INDEX departments_slug_gin ON departments USING GIN (slug)');
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
