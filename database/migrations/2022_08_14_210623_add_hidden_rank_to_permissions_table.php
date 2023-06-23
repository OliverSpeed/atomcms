<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('permissions_groups', function (Blueprint $table) {
            if (Schema::hasColumn('permissions_groups', 'hidden_rank')) {
                Schema::dropColumns('permissions_groups', 'hidden_rank');
            }

            $table->boolean('hidden_rank')->after('name')->default(false);
        });
    }

    public function down()
    {
        Schema::table('permissions_groups', function (Blueprint $table) {
        });
    }
};