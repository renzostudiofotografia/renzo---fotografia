<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false);
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('section')->unique();
            $table->json('draft');
            $table->json('published')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('alt')->default('');
            $table->string('provider')->default('local');
            $table->string('path');
            $table->string('cloudflare_id')->nullable();
            $table->text('cloudflare_url')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('site_settings');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }
};
