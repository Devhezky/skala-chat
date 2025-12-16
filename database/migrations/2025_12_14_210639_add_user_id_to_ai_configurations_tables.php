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
        Schema::table('ai_sources', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->after('id')->nullable();
        });

        Schema::table('ai_agent_configs', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_sources', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('ai_agent_configs', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
