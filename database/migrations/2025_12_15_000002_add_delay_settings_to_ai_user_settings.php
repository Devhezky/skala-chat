<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_user_settings', 'delay_type')) {
                $table->string('delay_type')->default('fixed')->after('enable_split_chat'); // 'fixed' or 'smart'
            }
            if (!Schema::hasColumn('ai_user_settings', 'min_delay')) {
                $table->integer('min_delay')->default(3)->after('delay_type'); // seconds
            }
            if (!Schema::hasColumn('ai_user_settings', 'max_delay')) {
                $table->integer('max_delay')->default(5)->after('min_delay'); // seconds
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('ai_user_settings', 'delay_type')) {
                $table->dropColumn('delay_type');
            }
            if (Schema::hasColumn('ai_user_settings', 'min_delay')) {
                $table->dropColumn('min_delay');
            }
            if (Schema::hasColumn('ai_user_settings', 'max_delay')) {
                $table->dropColumn('max_delay');
            }
        });
    }
};
