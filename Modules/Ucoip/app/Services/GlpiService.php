<?php

namespace Modules\Ucoip\Services;

use Modules\Ucoip\Models\GlpiUser;

class GlpiService{

 public function hashPassword($textPassword){
    $options = [
        'cost' => 10,
    ];
    $passwordHash = password_hash($textPassword, PASSWORD_BCRYPT, $options);
    return $passwordHash;
 }


 public function updateGlpiUser($idUser, $nombres, $apellidos, $password, $idArea, $idDepartamento, $idPuestos){

    $userGlpi = GlpiUser::find($idUser);

    if($userGlpi){
        $userGlpi->firstname = $nombres;
        $userGlpi->realname = $apellidos;
        if(!empty($password)){
            $userGlpi->password = $this->hashPassword($password);
        }
        $userGlpi->password_last_update = now();
        $userGlpi->id_areas_directorio = $idArea; 
        $userGlpi->id_departamentos_directorio = $idDepartamento;
        $userGlpi->id_puesto_directorio = $idPuestos; 

        $userGlpi->save();
    }

    return $userGlpi;
 }

}