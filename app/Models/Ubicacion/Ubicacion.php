<?php

namespace App\Models\Ubicacion;

use App\Utilidades\GeneradorThumbnail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ubicacion extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    protected $table = "ubicaciones";

    protected $casts = [
        // 'archivos' => 'array',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function empresa()
    {
        return $this->hasOne(\App\Models\Empresa\Empresa::class, 'id', 'id_empresa');
    }

    public function getPositionAttribute(){
        return [
            'lat'=>$this->lat,
            'lng'=>$this->lng,
        ];
    }

    public function toArray(){
        $array = parent::toArray();
        $array['position'] = $this->position;
        $array['title'] = $this->nombre;
        $array['letter_id'] = strtolower( $this->nameFromNumber( $this->id - 1) );
        $array['archivos'] = GeneradorThumbnail::generar($this->archivos);
        $array['kml_features'] = json_decode($this->kml_features);
        return $array;
    }

    function nameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->nameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
}
