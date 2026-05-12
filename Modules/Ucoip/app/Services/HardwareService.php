<?php

namespace Modules\Ucoip\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\HardwarePcModel;

class HardwareService{

    public function storeHardware($data){
        $hardware  =  new HardwarePcModel();

        $hardware->marca            = $data['marca'];
        $hardware->modelo           = $data['modelo'];
        $hardware->no_serie         = $data['no_serie'];
        $hardware->tipo             = $data['tipo'];
        $hardware->mac              = $data['mac'];
        $hardware->memoria_ram      = $data['memoria_ram'];
        $hardware->disco_duro       = $data['disco_duro'];
        $hardware->procesador       = $data['procesador'];
        $hardware->caracteristicas  = $data['caracteristicas'];
        $hardware->observaciones    = $data['observaciones'];
        $hardware->estado           = $data['estado'];
        $hardware->cat_hardware_id  = $data['cat_hardware_id'];
        $hardware->cat_empresa_id   = $data['cat_empresa_id'];
        $hardware->save();

        return $hardware;
    }

    public function updateEstatusHardware($id, $estado){
        $hardware =  HardwarePcModel::find($id);
        if($hardware){
            $hardware->estado = $estado;
        }
    }



}