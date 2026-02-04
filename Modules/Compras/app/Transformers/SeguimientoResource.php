<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EstatusSolicitud;
use Illuminate\Support\Facades\DB;

class SeguimientoResource extends JsonResource
{
    private static $usuariosCache = [];

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        $evento = $this->setLabelEvento($this->event);
        $datosCambio = $this->parseCambio($this->new_values);
        $mensajeCambio = $this->generarMensaje($this->event, $datosCambio, $this->user_id);
        return [
            'id' => $this->id,
            'idSolicitud' => $this->record_id,
            'evento' => $evento,
            'mensaje' => $mensajeCambio,
            'fecha' => $this->created_at
        ];
    }

    private function setLabelEvento($evento)
    {

        $estados = [
            "created" => "Inicio del proceso",
            "updated" => "Se actualizó",
        ];

        return $estados[$evento] ?? 'Evento desconocido';
    }

    private function generarMensaje($evento, $values, $userId)
    {
        $nombreUsuarios = $this->getNombreUsuario($userId);

        switch ($evento) {
            case 'created':
                return 'Se creó tu solicitud con folio: ' . ($values['folio'] ?? 'SIN FOLIO') . "\n" . ' por ' . $nombreUsuarios;

            case 'updated':
                if (
                    (array_key_exists('auto_gg', $values) && $values['auto_gg'] === 0) ||
                    (array_key_exists('auto_admin', $values) && $values['auto_admin'] === 0)
                ) {
                    return 'Se requiere autorización de gerencia administrativa y gerencia general';
                }

                $autorizaciones = [
                    'auto_gg' => 'Autorizó gerencia general',
                    'auto_admin' => 'Autorizó gerencia administrativa',
                    'auto_macro' => 'Autorizó Macrotaller',
                ];

                foreach ($autorizaciones as $key => $mensaje) {
                    if (!empty($values[$key])) {
                        return $mensaje . "\n" . ' - por ' . $nombreUsuarios;
                    }
                }

                if (isset($values['estatus'])) {
                    $labels = EstatusSolicitud::labels();
                    $label = $labels[$values['estatus']] ?? 'DESCONOCIDO';
                    $motivo = isset($values['motivo_revision']) ? ' - Devuelta por la siguiente razón: '.$values['motivo_revision'] : ''; 
                    return 'El estatus de tu solicitud cambió a: ' . $label . "\n" . $motivo."\n" . ' - por: ' . $nombreUsuarios;
                }

                if (isset($values['motivo_revision'])) {
                    return 'La solicitud fue devuelta para su revision por la siguiente razón: '.$values['motivo_revision']. ' - por: ' . $nombreUsuarios ;
                }

                if (isset($values['motivo_revision']) && empty($values['motivo_revision'])) {
                    return 'La solicitud fue modificada'. ' - por: ' . $nombreUsuarios ;
                }

                break;
        }

        return 'No se pudo generar el mensaje para el evento especificado.';
    }

    private function parseCambio($cambio)
    {
        if (is_array($cambio)) return $cambio ?? null;
        return json_decode($cambio, true);
    }

    private function getNombreUsuario($usuarioId)
    {
        if (isset(self::$usuariosCache[$usuarioId])) {
            return self::$usuariosCache[$usuarioId];
        }

        $usuario = DB::connection('intranet')->select('SELECT firstname, realname FROM glpi_users WHERE id = ?', [$usuarioId]);

        if (!empty($usuario)) {
            $nombreCompleto = $usuario[0]->firstname . ' ' . $usuario[0]->realname;
            self::$usuariosCache[$usuarioId] = $nombreCompleto;
            return $nombreCompleto;
        }

        return 'Usuario desconocido';
    }
}

