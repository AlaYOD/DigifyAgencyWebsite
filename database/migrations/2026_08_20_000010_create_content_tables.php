<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('slug');
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('slug');
            $table->jsonb('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('slug');
            $table->jsonb('title');
            $table->jsonb('excerpt')->nullable();
            $table->jsonb('body');
            $table->jsonb('seo')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedSmallInteger('reading_time')->default(1);
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_tag', function (Blueprint $table): void {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('slug');
            $table->jsonb('title');
            $table->jsonb('excerpt')->nullable();
            $table->jsonb('blocks')->default('[]');
            $table->jsonb('seo')->nullable();
            $table->string('template')->default('default');
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_homepage')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('slug');
            $table->string('client_name');
            $table->jsonb('title');
            $table->jsonb('summary')->nullable();
            $table->jsonb('blocks')->default('[]');
            $table->string('sector')->nullable()->index();
            $table->string('discipline')->nullable()->index();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->jsonb('name');
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->restrictOnDelete();
            $table->jsonb('label');
            $table->nullableMorphs('linkable');
            $table->text('url')->nullable();
            $table->string('target')->default('same');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });

        foreach (['categories' => ['slug', 'name'], 'tags' => ['slug', 'name'], 'posts' => ['slug', 'title', 'body', 'seo'], 'pages' => ['slug', 'title', 'blocks', 'seo'], 'projects' => ['slug', 'title', 'blocks'], 'menus' => ['name'], 'menu_items' => ['label']] as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("CREATE INDEX {$table}_{$column}_gin ON {$table} USING GIN ({$column})");
            }
        }

        foreach (['categories', 'tags', 'posts', 'pages', 'projects'] as $table) {
            DB::statement("CREATE UNIQUE INDEX {$table}_slug_en_unique ON {$table} ((slug->>'en')) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX {$table}_slug_ar_unique ON {$table} ((slug->>'ar')) WHERE deleted_at IS NULL AND slug->>'ar' IS NOT NULL");
        }

        DB::statement('CREATE UNIQUE INDEX pages_single_homepage ON pages (is_homepage) WHERE is_homepage = true AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
