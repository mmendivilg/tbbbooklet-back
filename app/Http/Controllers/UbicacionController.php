<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ubicacion\Ubicacion;
use App\Validaciones\Ubicacion\UbicacionValidacion;

class UbicacionController extends Controller
{
    public function index()
    {
        $ubicaciones = Ubicacion::orderBy('id', 'DESC')->get();
        return $ubicaciones;
    }

    public function store(Request $request)
    {
        try {
            $validacion = new UbicacionValidacion( null, $request->all() );
            if( !$validacion->esValido() ) {
                return response()->json( array('errors' => $validacion->errores() ), 400 );
            }

            $ubicacion = new Ubicacion();
            $ubicacion->id_empresa = 1;
            $ubicacion->nombre = $request->nombre;
            $ubicacion->lat = $request->lat;
            $ubicacion->lng = $request->lng;
            $ubicacion->kml_features = json_encode($request->kml_features);
            $ubicacion->save();

            $ubicacion = Ubicacion::find($ubicacion->id);

            return $ubicacion;
        } catch (\Exception | \Throwable | \Error $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $ubicacion = Ubicacion::find($id);
        return $ubicacion;
    }

    public function update($id, Request $request)
    {
        try {
            $validacion = new UbicacionValidacion( null, $request->all() );
            if( !$validacion->esValido() ) {
                return response()->json( array('errors' => $validacion->errores() ), 400 );
            }

            $ubicacion = Ubicacion::find($id);
            $ubicacion->nombre = $request->nombre;
            $ubicacion->lat = $request->lat;
            $ubicacion->lng = $request->lng;
            $ubicacion->kml_features = json_encode($request->kml_features);
            $ubicacion->save();

            return $ubicacion;
        } catch (\Exception | \Throwable | \Error $e) {
            return response()->json( ["error" => $e->getMessage()], 500 );
        }
    }

    public function destroy($id)
    {
        Ubicacion::find($id)->delete();
    }
}
