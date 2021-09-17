<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeaturesUbicacionesTable extends Migration
{
    public function up()
    {
        Schema::table('ubicaciones', function($table)
        {
            $table->json('kml_features')->nullable()->after('archivos');

        });
    }

    public function down()
    {
        Schema::table('ubicaciones', function($table)
        {
            $table->dropColumn('kml_features');
        });
    }
}
