<?php

namespace Modules\Ucoip\Services;

use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use Modules\Ucoip\Models\TokenAgencia;
use Modules\Ucoip\Models\TokensUcoip;

class TokensService{

    protected $cifradoService;

    public function __construct(
        CifradoService $cifradoService,
    ){
        $this->cifradoService = $cifradoService;
    }

    /**
     * Saves or updates a token and returns the result message and data.
     *
     * @param int|null $id            The existing token ID (optional).
     * @param string   $token         The token value to save.
     * @param int      $puestoMarca  The associated puesto marca ID.
     * @param int      $empresa       The associated empresa ID.
     * @param string|null $observaciones The optional observation text.
     *
     */
    public function guardar(
        ?int $id,
        string $token,
        int $puestoMarca,
        int $empresa,
        ?string $observaciones = null
    )
    {
        if ($id) {
            $tokenAgencia = TokenAgencia::findOrFail($id);
            $mensaje = 'Registro actualizado con éxito';
        } else {

            if (TokenAgencia::where('token', $token)->exists()) {
                throw new \Exception('Este token ya existe en otra sucursal');
            }

            $tokenAgencia = new TokenAgencia();
            $tokenAgencia->activo = 1;
            $mensaje = 'Registro creado con éxito';
        }

        $tokenAgencia->token = $token;
        $tokenAgencia->ucoip_puesto_marca_id = $puestoMarca;
        $tokenAgencia->ucoip_cat_empresas_id = $empresa;
        $tokenAgencia->observaciones = $observaciones;

        $tokenAgencia->save();

        return [
            'message' => $mensaje,
            'data' => $tokenAgencia
        ];
    }


    /**
     * Assigns a token to a user and saves the assignment.
     *
     * @param int   $idUcoip     The UCOIP ID.
     * @param int   $idToken       The token agency ID.
     * @param string $usuario      The user's name.
     * @param string $claveAcceso  The encrypted access key.
     * @param string $password    The encrypted password.
     * @param string $fechaAsignacion The assignment date and time.
     */
    public function asiganarToken($idUcoip, $idToken, $usuario, $claveAcceso, $password, $fechaAsignacion ){
        $asignacion =  new TokensUcoip();

            $asignacion->ucoip_ucoip_id =  $idUcoip;
            $asignacion->ucoip_token_agencias_id =  $idToken;
            $asignacion->usuario =  $usuario;

            $asignacion->acceso =  $this->cifradoService->encrypt($claveAcceso,);
            $asignacion->contrasenia =  $this->cifradoService->encrypt($password);
            $asignacion->fecha_asignacion =  $fechaAsignacion;
            $asignacion->save();

        $this->updateStatusToken($asignacion->ucoip_token_agencias_id, EstatusActivos::ASIGNADA);

        return $asignacion;
    }


    /**
     * Finalizes the assignment of a token and updates the token status.
     *
     * @param int $idToken The token agency ID to finalize.
     */
    public function finalizarAsignacionToken($idToken){
        $asignacion = TokensUcoip::find($idToken);
        if($asignacion){
            $asignacion->fecha_retiro = now();
            $asignacion->estatus = EstatusAsignaciones::INACTIVA;
            $asignacion->save();
        }

        $this->updateStatusToken($asignacion->ucoip_token_agencias_id, EstatusActivos::DISPONIBLE);

        return $asignacion;
    }

    /**
     * Updates the status of a token.
     *
     * @param int   $id      The token agency ID to update.
     * @param string $status The new status for the token.
     */
    public function updateStatusToken($id, $status){
        $software = TokenAgencia::find($id);

        if($software){
            $software->estatus = $status;
            $software->save();
        }
    }

    /**
     * Descifra el password using the CifradoService.
     *
     * @param string $valor The encrypted value to decrypt.
     */
    public function descifrarPassword($valor){
        return  $this->cifradoService->decrypt($valor);
    }


}
