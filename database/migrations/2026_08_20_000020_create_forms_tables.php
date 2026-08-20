<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->jsonb('submit_label');
            $table->jsonb('success_message');
            $table->text('redirect_url')->nullable();
            $table->jsonb('notify_emails')->default('[]');
            $table->text('webhook_url')->nullable();
            $table->boolean('stores_submissions')->default(true);
            $table->boolean('captcha_enabled')->default(false);
            $table->unsignedSmallInteger('retention_days')->default(730);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('type');
            $table->jsonb('label');
            $table->jsonb('placeholder')->nullable();
            $table->jsonb('help_text')->nullable();
            $table->jsonb('options')->nullable();
            $table->jsonb('rules')->default('[]');
            $table->jsonb('conditional_logic')->nullable();
            $table->string('width')->default('full');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['form_id', 'key']);
            $table->index(['form_id', 'sort_order']);
        });

        Schema::create('form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->jsonb('data');
            $table->jsonb('meta')->nullable();
            $table->unsignedSmallInteger('spam_score')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['form_id', 'created_at']);
            $table->index('read_at');
        });

        foreach (['name', 'description', 'submit_label', 'success_message', 'notify_emails'] as $column) {
            DB::statement("CREATE INDEX forms_{$column}_gin ON forms USING GIN ({$column})");
        }

        foreach (['label', 'options', 'rules', 'conditional_logic'] as $column) {
            DB::statement("CREATE INDEX form_fields_{$column}_gin ON form_fields USING GIN ({$column})");
        }

        DB::statement('CREATE INDEX form_submissions_data_gin ON form_submissions USING GIN (data)');
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
