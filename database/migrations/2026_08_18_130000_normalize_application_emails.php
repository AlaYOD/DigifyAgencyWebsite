<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_applications')->update([
            'email' => DB::raw('LOWER(BTRIM(email))'),
        ]);
    }

    public function down(): void
    {
        // Email normalization is intentionally irreversible.
    }
};
