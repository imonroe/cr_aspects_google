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

      $aspect_type = new AspectType;
      $aspect_type->aspect_name = 'Google Contacts API Results';
      $aspect_type->aspect_description = 'The full cached results of a call to the Google Contacts API';
      $aspect_type->is_viewable = 0;
      $aspect_type->save();

      $aspect_type = new AspectType;
      $aspect_type->aspect_name = 'Google Contact Data';
      $aspect_type->aspect_description = 'An individual Google Contact entity';
      $aspect_type->is_viewable = 1;
      $aspect_type->save();

      $aspect_type = new AspectType;
      $aspect_type->aspect_name = 'Google Tasks List';
      $aspect_type->aspect_description = 'A Google Task List';
      $aspect_type->is_viewable = 1;
      $aspect_type->save();

      $aspect_type = new AspectType;
      $aspect_type->aspect_name = 'Google Calendar';
      $aspect_type->aspect_description = 'A Google Calendar';
      $aspect_type->is_viewable = 1;
      $aspect_type->save();


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
