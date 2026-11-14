<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('landing_leads', 'inquiry_details')) {
            Schema::table('landing_leads', function (Blueprint $table): void {
                $table->json('inquiry_details')->nullable();
            });
        }
        if (! Schema::hasColumn('landing_pages', 'previous_slugs')) {
            Schema::table('landing_pages', function (Blueprint $table): void {
                $table->json('previous_slugs')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('landing_leads', 'inquiry_details')) {
            Schema::table('landing_leads', function (Blueprint $table): void {
                $table->dropColumn('inquiry_details');
            });
        }
        if (Schema::hasColumn('landing_pages', 'previous_slugs')) {
            Schema::table('landing_pages', function (Blueprint $table): void {
                $table->dropColumn('previous_slugs');
            });
        }
    }
};
