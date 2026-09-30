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
        if (Schema::hasTable('master_items') && !Schema::hasColumn('master_items', 'foto')) {
            Schema::table('master_items', function (Blueprint $table) {
                $table->string('foto')->nullable()->after('jenis');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('master_items') && Schema::hasColumn('master_items', 'foto')) {
            Schema::table('master_items', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
};
