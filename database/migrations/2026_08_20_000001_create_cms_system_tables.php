<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->jsonb('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->jsonb('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('batch_uuid');
        });

        Schema::create('revisions', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('revisionable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label')->nullable();
            $table->jsonb('payload');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['revisionable_type', 'revisionable_id', 'created_at']);
        });

        DB::statement('CREATE INDEX activity_log_properties_gin ON activity_log USING GIN (properties)');
        DB::statement('CREATE INDEX revisions_payload_gin ON revisions USING GIN (payload)');

        $now = now();
        DB::table('settings')->insert([
            ['group' => 'site', 'name' => 'site_name', 'payload' => json_encode('Digify'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'site_tagline', 'payload' => json_encode(['en' => 'Digital experiences with impact', 'ar' => 'تجارب رقمية تصنع الأثر']), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'default_locale', 'payload' => json_encode('en'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'fallback_locale', 'payload' => json_encode('en'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'contact_email', 'payload' => json_encode('hello@digify.agency'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'social_links', 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'analytics_enabled', 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'maintenance_message', 'payload' => json_encode(['en' => '', 'ar' => '']), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'site', 'name' => 'cookie_consent_enabled', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('settings');
    }
};
