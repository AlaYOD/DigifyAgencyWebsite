<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->char('locale', 2);
            $table->string('source')->nullable();
            $table->smallInteger('ai_score')->nullable();
            $table->jsonb('ai_summary')->nullable();
            $table->smallInteger('rating')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestampTz('applied_at');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['job_posting_id', 'email']);
            $table->index(['job_posting_id', 'pipeline_stage_id']);
            $table->index(['pipeline_stage_id', 'applied_at']);
            $table->index('email');
        });

        DB::statement('ALTER TABLE job_applications ADD CONSTRAINT job_applications_ai_score_range CHECK (ai_score IS NULL OR (ai_score >= 0 AND ai_score <= 100))');
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
