<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('reference_code', 20)->unique();
            $table->jsonb('title');
            $table->jsonb('slug');
            $table->jsonb('summary');
            $table->jsonb('description');
            $table->jsonb('responsibilities');
            $table->jsonb('requirements');
            $table->jsonb('benefits');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship', 'temporary']);
            $table->enum('workplace_type', ['on_site', 'hybrid', 'remote']);
            $table->string('city')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'lead', 'executive']);
            $table->smallInteger('experience_years_min')->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->char('salary_currency', 3)->nullable();
            $table->enum('salary_period', ['hour', 'month', 'year'])->nullable();
            $table->boolean('salary_is_public')->default(false);
            $table->smallInteger('positions_count')->default(1);
            $table->enum('status', ['draft', 'published', 'paused', 'closed', 'archived'])->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('closes_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->jsonb('seo')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['status', 'published_at']);
            $table->index(['status', 'closes_at']);
            $table->index('department_id');
        });

        DB::statement('CREATE INDEX job_postings_slug_gin ON job_postings USING GIN (slug)');
        DB::statement('CREATE INDEX job_postings_title_gin ON job_postings USING GIN (title)');
        DB::statement('ALTER TABLE job_postings ADD CONSTRAINT job_postings_salary_order CHECK (salary_max IS NULL OR salary_min IS NULL OR salary_max >= salary_min)');
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
