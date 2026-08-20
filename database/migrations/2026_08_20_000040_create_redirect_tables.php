<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_path')->unique();
            $table->text('to_url');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->char('locale', 2)->nullable();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales')->nullOnDelete();
        });

        Schema::create('redirect_misses', function (Blueprint $table): void {
            $table->id();
            $table->string('path')->unique();
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('hits')->default(1);
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirect_misses');
        Schema::dropIfExists('redirects');
    }
};
