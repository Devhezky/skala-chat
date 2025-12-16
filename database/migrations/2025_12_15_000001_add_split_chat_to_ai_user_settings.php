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
            if (!Schema::hasColumn('ai_user_settings', 'enable_split_chat')) {
                $table->boolean('enable_split_chat')->default(0)->after('max_history_limit');
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
            if (Schema::hasColumn('ai_user_settings', 'enable_split_chat')) {
                $table->dropColumn('enable_split_chat');
            }
        });
    }
};
