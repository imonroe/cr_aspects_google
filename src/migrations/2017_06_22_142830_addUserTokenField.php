<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserTokenField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasColumn('users', 'google_token')) {
			Schema::table('users', function (Blueprint $table) {
				$table->text('google_token')->nullable()->default(null);
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
		if (Schema::hasColumn('users', 'google_token')) {
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn('google_token');
			});
		}
    }
}
