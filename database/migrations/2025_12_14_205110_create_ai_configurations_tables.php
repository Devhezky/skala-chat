<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ai_assistant_id')->nullable(); 
            $table->string('type'); // web, pdf, faq
            $table->longText('content'); // URL, File Path, or JSON for FAQ
            $table->boolean('status')->default(0); // 0: Untrained, 1: Trained
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_agent_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ai_assistant_id')->nullable();
            $table->text('trigger_condition')->nullable();
            $table->text('waiting_message')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_sources');
        Schema::dropIfExists('ai_agent_configs');
    }
};
