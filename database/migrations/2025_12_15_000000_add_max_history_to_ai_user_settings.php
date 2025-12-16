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
            if (!Schema::hasColumn('ai_user_settings', 'max_history_limit')) {
                $table->integer('max_history_limit')->default(10)->after('system_prompt');
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
            if (Schema::hasColumn('ai_user_settings', 'max_history_limit')) {
                 $table->dropColumn('max_history_limit');
            }
        });
    }
};
