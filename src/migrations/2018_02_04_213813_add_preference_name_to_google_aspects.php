<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use imonroe\crps\AspectType;

class AddPreferenceNameToGoogleAspects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $aspect_type = AspectType::where('aspect_name', '=', 'Google Contacts API Results')->firstOrFail();
        $aspect_type->preference_name = 'google_aspects_enabled';
        $aspect_type->save();

        $aspect_type = AspectType::where('aspect_name', '=', 'Google Contact Data')->firstOrFail();
        $aspect_type->preference_name = 'google_aspects_enabled';
        $aspect_type->save();

        $aspect_type = AspectType::where('aspect_name', '=', 'Google Tasks List')->firstOrFail();
        $aspect_type->preference_name = 'google_aspects_enabled';
        $aspect_type->save();

        $aspect_type = AspectType::where('aspect_name', '=', 'Google Calendar')->firstOrFail();
        $aspect_type->preference_name = 'google_aspects_enabled';
        $aspect_type->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
