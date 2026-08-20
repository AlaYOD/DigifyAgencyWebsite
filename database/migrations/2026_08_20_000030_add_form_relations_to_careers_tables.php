<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->foreignId('form_id')->nullable()->after('department_id')->constrained('forms')->restrictOnDelete();
        });

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->foreignId('form_submission_id')->nullable()->after('job_posting_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('form_submission_id');
        });

        Schema::table('job_postings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('form_id');
        });
    }
};
