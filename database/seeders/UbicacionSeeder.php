<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ubicacion\Ubicacion;

class UbicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ubicacion::query()->truncate();
        Ubicacion::create(
            [
                'nombre' => 'Colinas',
                'lat' => '22.88120024637345',
                'lng' => '-109.94021165433583',
                'id_empresa' => 1,
            ]
        );
        Ubicacion::create(
            [
                'nombre' => 'Cerro de la Z',
                'lat' => '22.881951475149418',
                'lng' => '-109.92853868070301',
                'id_empresa' => 1,
            ]
        );
        Ubicacion::create(
            [
                'nombre' => 'Pedregal',
                'lat' => '22.878867456837',
                'lng' => '-109.91815316739735',
                'id_empresa' => 1,
            ]
        );
    }
}
