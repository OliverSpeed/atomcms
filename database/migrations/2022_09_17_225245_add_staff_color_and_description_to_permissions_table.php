<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('permissions_groups', function (Blueprint $table) {
            $table->string('staff_color', 8)->default('#327fa8')->after('badge_code');
        });
    }

    public function down()
    {
        Schema::table('permissions_groups', function (Blueprint $table) {
            //
        });
    }
};