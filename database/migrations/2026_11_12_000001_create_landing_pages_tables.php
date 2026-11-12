<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 120)->unique();
            $table->json('draft');
            $table->json('published')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_leads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->uuid('submission_id')->unique();
            $table->string('name', 160);
            $table->string('phone', 30);
            $table->string('email', 160)->nullable();
            $table->date('event_date')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('consent_at');
            $table->string('utm_source', 200)->nullable();
            $table->string('utm_medium', 200)->nullable();
            $table->string('utm_campaign', 200)->nullable();
            $table->string('utm_content', 200)->nullable();
            $table->string('utm_term', 200)->nullable();
            $table->string('fbclid', 500)->nullable();
            $table->timestamps();
            $table->index(['landing_page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_leads');
        Schema::dropIfExists('landing_pages');
    }
};
