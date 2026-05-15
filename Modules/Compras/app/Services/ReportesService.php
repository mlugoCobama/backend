<?php

namespace Modules\Compras\Services;

use App\Enums\EstatusSolicitud;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\SolicitudesCompra;

class ReportesService{
    private $usuariosCache = [];
    private $rawEmpresas = [
            333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',

            131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
            210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
            111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 240 => 'SERVIGAS DEL VALLE', 132 => 'GAS PREMIO',
            200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
            133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 
            191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA', 353111 => 'GAS URBANO - GARZA SUR', 251250 => 'FLAMAMEX - FLAMAZUL',

            710 => 'NISSAN UNIVERSIDAD',
            7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
             2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
    ];


    /**
     *  Query que recupera solicitudes por estatus
     */
    public function querySolicitudesByStatus( $tipo, $estatus, $fechaInicial, $fechaFinal)
    {
        $empresas =  $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();

        $solicitudes = SolicitudesCompra::with([
            'DetallesSolicitud.unidadMedida',
            'Cotizaciones.CotizacionesProveedor.datos_proveedor',
            'SistemaMantenimiento',
            'TipoMantenimiento'
        ])
            ->where('estatus', $estatus)
            ->where('activo', 1)
            ->where('tipo', $tipo)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->whereHas('DetallesSolicitud', function($q) {
                $q->where('confirmado', 1);
            })
            ->when($tipo == 2 && $estatus > 1, function ($query) {
                    $query->where('auto_admin', 1)
                            ->where('auto_gg', 1)
                            ->where('auto_macro', 1);
            })
            ->when(($tipo == 1) && $estatus > 1, function ($query) {
                    $query->where('auto_admin', 1)->where('auto_gg', 1);
            })->get()
            ->flatMap(function ($solicitud) use ($empresas, $labels) {
                
                $detalles = $solicitud->DetallesSolicitud;
                $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';

                return $detalles->map(function ($detalle, $index) use ($solicitud, $empresas, $label) {
                    $proveedorSeleccionado = $solicitud->Cotizaciones->flatMap->CotizacionesProveedor->firstWhere('seleccionado', 1);
                    $tipoMantenimiento =  $solicitud->TipoMantenimiento->nombre ?? '';
                    $sistemaMantenimiento =  $solicitud->SistemaMantenimiento->sistema ?? '';
                    $proveedor = $proveedorSeleccionado->datos_proveedor->nombre ?? 'Por definir';
                    return [
                        'Folio'        => $index === 0 ? $solicitud->folio : '',
                        'Fecha'        => $index === 0 ? date('d/m/Y H:i', strtotime($solicitud->fecha)) : '',
                        'Empresa'      => $index === 0 ? ($empresas[$solicitud->empresa] ?? 'N/A') : '',
                        'Estado'       => $index === 0 ? $label : '',
                        'Cantidad'     => $detalle->cantidad ?? 0,
                        'Unidad'       => $detalle->unidadMedida->nombre ?? '',
                        'Descripción'  => $detalle->descripcion ?? '',
                        'Observaciones'=> $detalle->observaciones ?? '',
                        'Proveedor'=> $index === 0 ? $proveedor : '',
                        'tipoMantenimiento'  => $index === 0 ? $tipoMantenimiento : '',
                        'sistemaMantenieminto' => $index === 0 ? $sistemaMantenimiento : '',
                    ];
                });
            });
        
        return $solicitudes;
    }

    /**
     * Primer version para recuperar un reporte general de compras por empresa
     */
    public function queryComprasGneralesEstable( $empresa, $tipo = null ){

        $empresas = $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();

        $solicitudes = SolicitudesCompra::with([
            'DestinoVehiculo',
            'SistemaMantenimiento',
            'Cotizaciones.orden_compra',
            'DetallesSolicitud.unidadMedida',
            'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
        ])
            ->where('estatus','>', EstatusSolicitud::ESP_AUT_PLANTA)
            ->where('estatus','<>', EstatusSolicitud::CANCELADA)
            ->where('activo', 1)
            ->where('tipo', $tipo)
            ->where('empresa', $empresa)
            ->whereHas('DetallesSolicitud', function($q) {
                $q->where('confirmado', 1);
            })
        ->get()
        ->flatMap(function ($solicitud) use ($empresas, $labels) {

            //   OBTENER LA COTIZACIÓN QUE TIENE UNA ORDEN DE COMPRA
            $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
            $folioOC = $cotizacionOC->orden_compra->folio_oc ?? '';
            $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';
            $rows = [];
            $subtotal = 0;

            //   CALCULAR SUBTOTAL
            foreach ($solicitud->DetallesSolicitud as $detalle) {

                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );

                $precio = (float) ($cotSel->importe_unitario ?? 0);
                $cantidad = $detalle->cantidad ?? 1;

                $subtotal += $precio * $cantidad;
            }

            // $iva = $subtotal * 0.16;
            // $total = $subtotal + $iva;
            // $proveedor = $cotSel->CotizacionesProveedores->datos_proveedor->nombre ?? 'N/A';

            $destinoFormat = $this->getUsuario($solicitud->usuario_destino);
            $sistemaMantenimiento =  $solicitud->SistemaMantenimiento->sistema ?? '';

            //   FILA PRINCIPAL: TOTALES DE LA SOLICITUD
            $rows[] = [
                'Folio'         => $solicitud->folio,
                'Folio_OC'      => $folioOC,
                'Fecha'         => date('d/m/Y H:i', strtotime($solicitud->fecha)),
                'Empresa'       => $empresas[$solicitud->empresa] ?? 'N/A',
                'Destino'       => $destinoFormat['nombre'],
                'Area'         =>  $destinoFormat['area'],
                'Estado'        => $label,
                'Cantidad'      => '',
                'Descripcion'   => '',
                'Observaciones' => '',
                'Unidad'        => '',
                'Precio'        => '',
                'tipo'          => $sistemaMantenimiento
            ];

            //   FILAS DETALLE DE LA SOLICITUD
            foreach ($solicitud->DetallesSolicitud as $detalle) {
                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );
    
                $precio = (float) ($cotSel->importe_unitario ?? 0);
    
                $rows[] = [
                    'Folio'         => '',
                    'Folio_OC'      => $folioOC,
                    'Fecha'         => '',
                    'Empresa'       => '',
                    'Destino'       => '',
                    'Area'         =>  '',
                    'Estado'        => '',
                    'Cantidad'      => $detalle->cantidad ?? 0,
                    'Descripcion'   => $detalle->descripcion ?? '',
                    'Observaciones' => $detalle->observaciones ?? '',
                    'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                    'Precio'        => $precio,
                    'tipo'          => ''
                ];
            }

            return $rows;
        });

            return $solicitudes;
    }
    
    /**
     * Version optimizada para recuperar un reporte general de compras por empresa
     * Esta query pude ser utilizada para compras de generales, recursos tecnológicos y autos
     */
    public function queryComprasGnerales($empresa, $tipo = null)
    {
        $empresas = $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();

        $solicitudes = SolicitudesCompra::with([
                'SistemaMantenimiento',
                'Cotizaciones.orden_compra',
                'DetallesSolicitud.unidadMedida',
                'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
            ])
            ->where('estatus', '>', EstatusSolicitud::ESP_AUT_PLANTA)
            ->where('estatus', '<>', EstatusSolicitud::CANCELADA)
            ->where('activo', 1)
            ->where('tipo', $tipo)
            ->where('empresa', $empresa)
            ->whereHas('DetallesSolicitud', fn($q) => $q->where('confirmado', 1))
            ->get()
            ->flatMap(function ($solicitud) use ($empresas, $labels) {

                $rows = [];

                // Precalcular datos de solicitud 
                $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';

                $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
                $folioOC = $cotizacionOC->orden_compra->folio_oc ?? '';

                $fechaFormateada = $solicitud->fecha
                    ? date('d/m/Y H:i', strtotime($solicitud->fecha))
                    : '';

                $empresaNombre = $empresas[$solicitud->empresa] ?? 'N/A';

                $destinoFormat = $this->getUsuario($solicitud->usuario_destino);
                $sistemaMantenimiento = $solicitud->SistemaMantenimiento->sistema ?? '';

                $detallesProcesados = $solicitud->DetallesSolicitud->map(function ($detalle) {

                    $cotSel = $detalle->DetallesCotizacion
                        ->firstWhere(fn($cot) =>
                            $cot->CotizacionesProveedores &&
                            $cot->CotizacionesProveedores->seleccionado == 1
                        );

                    return [
                        'detalle' => $detalle,
                        'precio' => (float) ($cotSel->importe_unitario ?? 0),
                        'proveedor' => $cotSel?->CotizacionesProveedores?->datos_proveedor->nombre ?? 'N/A'
                    ];
                });

                $subtotal = $detallesProcesados->sum(fn($d) => $d['precio'] * ($d['detalle']->cantidad ?? 1));
                $iva = $subtotal * 0.16;
                // $total = $subtotal + $iva;

                $proveedor = $detallesProcesados->firstWhere('proveedor', '!=', 'N/A')['proveedor'] ?? 'N/A';

                // HEADER
                $rows[] = [
                    'Folio'         => $solicitud->folio,
                    'Folio_OC'      => $folioOC,
                    'Fecha'         => $fechaFormateada,
                    'Empresa'       => $empresaNombre,
                    'Destino'       => $destinoFormat['nombre'],
                    'Area'          => $destinoFormat['area'],
                    'Estado'        => $label,
                    'Cantidad'      => '',
                    'Descripcion'   => '',
                    'Observaciones' => '',
                    'Unidad'        => '',
                    'Precio'        => '',
                    'tipo'          => $sistemaMantenimiento
                ];

                // DETALLES
                foreach ($detallesProcesados as $item) {

                    $detalle = $item['detalle'];
                    $precio = $item['precio'];

                    $rows[] = [
                        'Folio'         => '',
                        'Folio_OC'      => $folioOC,
                        'Fecha'         => '',
                        'Empresa'       => '',
                        'Destino'       => '',
                        'Area'          => '',
                        'Estado'        => '',
                        'Cantidad'      => $detalle->cantidad ?? 0,
                        'Descripcion'   => $detalle->descripcion ?? '',
                        'Observaciones' => $detalle->observaciones ?? '',
                        'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                        'Precio'        => $precio,
                        'tipo'          => ''
                    ];
                }

                return $rows;
            });

        return $solicitudes;
    }

     /**
     * Recuperar un reporte general de compras de macrotaller por empresa
     * Uso exclusivo de macrotaller
     */
    public function queryComprasMacro( $empresa, $fechaInicio = null, $fechaFin = null , $tipo = null ){

        $empresas = $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();

        $solicitudes = SolicitudesCompra::with([
            'DestinoVehiculo', 'SistemaMantenimiento', 'TipoMantenimiento',
            'Cotizaciones.orden_compra',
            'DetallesSolicitud.DetalleAutotanque.DatosVehiculo',
            'DetallesSolicitud.unidadMedida',
            'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
        ])
        ->where('estatus','>', EstatusSolicitud::ESP_AUT_PLANTA)
        ->where('estatus','<>', EstatusSolicitud::CANCELADA)
        ->where('activo', 1)
        ->where('tipo', 2)
        ->where('empresa', $empresa)
        ->whereHas('DetallesSolicitud', function($q) {
            $q->where('confirmado', 1);
        })


        ->get()
        ->flatMap(function ($solicitud) use ($empresas, $labels) {


            //   OBTENER LA COTIZACIÓN QUE TIENE UNA ORDEN DE COMPRA
            $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
            $folioOC = $cotizacionOC->orden_compra->folio_oc ?? '';

            
            $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';

            $rows = [];
            $subtotal = 0;


            //   CALCULAR SUBTOTAL
            foreach ($solicitud->DetallesSolicitud as $detalle) {

                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );

                $precio = (float) ($cotSel->importe_unitario ?? 0);
                $cantidad = $detalle->cantidad ?? 1;

                $subtotal += $precio * $cantidad;
            }

            // $iva = $subtotal * 0.16;
            // $total = $subtotal + $iva;
            // $proveedor = $cotSel->CotizacionesProveedores->datos_proveedor->nombre ?? 'N/A';

            $destinoSolicitud = $solicitud->DestinoVehiculo->nro_economico ?? null;
            $destinoFormat = ($solicitud->tipo == 2 && $destinoSolicitud)
                ? "ECO: $destinoSolicitud"
                : 'N/A';
            // $tipoMantenimiento =  $solicitud->TipoMantenimiento->nombre ?? '';
            // $sistemaMantenimiento =  $solicitud->SistemaMantenimiento->sistema ?? '';

            //   FILA PRINCIPAL: TOTALES DE LA SOLICITUD
            $rows[] = [
                'Folio'         => $solicitud->folio,
                'Folio_OC'      => $folioOC,
                'Fecha'         => date('d/m/Y H:i', strtotime($solicitud->fecha)),
                'Empresa'       => $empresas[$solicitud->empresa] ?? 'N/A',
                'Destino'       => $destinoFormat,
                'Marca'         => $solicitud->DestinoVehiculo->marca ?? '',
                'SubMarca'     =>  $solicitud->DestinoVehiculo->submarca ?? '',
                'Modelo'        => $solicitud->DestinoVehiculo->modelo ?? '',
                'Serie'         => $solicitud->DestinoVehiculo->no_serie ?? '',
                'Estado'        => $label,
                'Cantidad'      => '',
                'Descripcion'   => '',
                'Observaciones' => '',
                'Unidad'        => '',
                'Precio'        => '',
            ];

            //   FILAS DETALLE DE LA SOLICITUD
            foreach ($solicitud->DetallesSolicitud as $detalle) {
                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );
    
                $precio = (float) ($cotSel->importe_unitario ?? 0);

                $ecoDetalle = $detalle->DetalleAutotanque->DatosVehiculo->nro_economico ?? null;
                $ecoDetalleFormat = ($solicitud->tipo == 2 && $ecoDetalle)
                    ? "ECO: $ecoDetalle"
                    : 'N/A';
                
                $rows[] = [
                    'Folio'         => '',
                    'Folio_OC'      => $folioOC,
                    'Fecha'         => '',
                    'Empresa'       => '',
                    'Destino'       => $ecoDetalleFormat,

                    'Marca'         => $detalle->DetalleAutotanque->DatosVehiculo->marca ?? '',
                    'SubMarca'     => $detalle->DetalleAutotanque->DatosVehiculo->submarca?? '',
                    'Modelo'        => $detalle->DetalleAutotanque->DatosVehiculo->modelo ?? '',
                    'Serie'         => $detalle->DetalleAutotanque->DatosVehiculo->no_serie ?? '',

                    'Estado'        => '',
                    'Cantidad'      => $detalle->cantidad ?? 0,
                    'Descripcion'   => $detalle->descripcion ?? '',
                    'Observaciones' => $detalle->observaciones ?? '',
                    'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                    'Precio'        => $precio,
                ];
            }

            return $rows;
        });

            return $solicitudes;
    }

    /**
     * Consulta las solicitudes de compra con sus detalles y los transforma en formato exportable.
     *
     * @param mixed $estatus Estado de la solicitud
     * @param mixed $tipo Tipo de solicitud
     */
    public function queryReporteSemanal( $empresa, $tipo ){
        $empresas = $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();
        $solicitudes = SolicitudesCompra::with([
            'DestinoVehiculo', 'SistemaMantenimiento', 'TipoMantenimiento',
            'Cotizaciones.orden_compra',
            'DetallesSolicitud.DetalleAutotanque.DatosVehiculo',
            'DetallesSolicitud.unidadMedida',
            'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
        ])
        ->where('estatus','<>', EstatusSolicitud::FINALIZADA)
        ->where('activo', 1)
        ->where('tipo', $tipo)
        ->where('empresa', $empresa)
        // ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->whereHas('DetallesSolicitud', function($q) {
            $q->where('confirmado', 1);
        })
        // ->whereHas('Cotizaciones.orden_compra', function ($q) {
        //     $q->where('pagado', 1);
        // })
        ->get()
        ->flatMap(function ($solicitud) use ($empresas, $labels) {

            //   OBTENER LA COTIZACIÓN QUE TIENE UNA ORDEN DE COMPRA
            $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
            $folioOC = $cotizacionOC->orden_compra->folio_oc ?? 'N/D';
            
            $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';
            $rows = [];
            $subtotal = 0;

            //   CALCULAR SUBTOTAL
            foreach ($solicitud->DetallesSolicitud as $detalle) {
                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) => $cot->CotizacionesProveedores && $cot->CotizacionesProveedores->seleccionado == 1);
                $precio = (float) ($cotSel->importe_unitario ?? 0);
                $cantidad = $detalle->cantidad ?? 1;
                $subtotal += $precio * $cantidad;
            }

            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;
            $proveedor = $cotSel->CotizacionesProveedores->datos_proveedor->nombre ?? 'N/A';

            $destinoSolicitud = $solicitud->DestinoVehiculo->nro_economico ?? null;
            $destinoFormat = ($solicitud->tipo == 2 && $destinoSolicitud) ? "ECO: $destinoSolicitud" : 'N/A';
            $tipoMantenimiento =  $solicitud->TipoMantenimiento->nombre ?? '';
            $sistemaMantenimiento =  $solicitud->SistemaMantenimiento->sistema ?? '';

            //   FILA PRINCIPAL: TOTALES DE LA SOLICITUD
            $rows[] = [
                'Fecha'         => date('d/m/Y H:i', strtotime($solicitud->fecha)),
                'Folio'         => $solicitud->folio,
                'Folio_OC'      => $folioOC,
                'Estado'        => $label,
                'Modificado'    => date('d/m/Y H:i', strtotime($solicitud->updated_at)),
                // 'Empresa'       => $empresas[$solicitud->empresa] ?? 'N/A',
                'Cantidad'      => '',
                'Unidad'        => '',
                'Descripcion'   => 'DETALLES DE LA SOLICITUD',
                'Observaciones' => 'TOTALES DE LA SOLICITUD',
                'Precio'        => '',
                'Subtotal'      => $subtotal,
                'IVA'           => $iva,
                'Total'         => $total,
                'Proveedor'     => $proveedor,
                'tipoMantenimiento'         => $tipoMantenimiento ?? 'N/A',
                'sistemaMantenieminto'         => $sistemaMantenimiento ?? 'N/A',
                'Destino'       => $destinoFormat, 
            ];

            //   FILAS DETALLE DE LA SOLICITUD
            foreach ($solicitud->DetallesSolicitud as $detalle) {

                $cotSel = $detalle->DetallesCotizacion
                ->firstWhere(fn($cot) => $cot->CotizacionesProveedores && $cot->CotizacionesProveedores->seleccionado == 1);
    
                $precio = (float) ($cotSel->importe_unitario ?? 0);

                $ecoDetalle = $detalle->DetalleAutotanque->DatosVehiculo->nro_economico ?? null;
                $ecoDetalleFormat = ($solicitud->tipo == 2 && $ecoDetalle) ? "ECO: $ecoDetalle" : 'N/A';
                
                $rows[] = [
                    'Folio'         => '',
                    'Folio_OC'      => '',
                    'Fecha'         => '',
                    // 'Empresa'       => '',
                    'Estado'        => '',
                    'Modificado'        => '',
                    'Cantidad'      => $detalle->cantidad ?? 0,
                    'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                    'Descripcion'   => $detalle->descripcion ?? '',
                    'Observaciones' => $detalle->observaciones ?? '',
                    'Precio'        => $precio,
                    'Subtotal'      => '',
                    'IVA'           => '',
                    'Total'         => '',
                    'Proveedor'     => '',
                    'tipoMantenimiento'  => '',
                    'sistemaMantenieminto' => '',
                    'Destino'       => $ecoDetalleFormat,
                ];
            }
            return $rows;
        });

            return $solicitudes;
        }

        /**
     * Consulta las solicitudes de compra con sus detalles y los transforma en formato exportable.
     *
     * @param mixed $estatus Estado de la solicitud
     * @param mixed $tipo Tipo de solicitud
     */
    public function queryDetallesSolicitudes( $empresa, $fechaInicio, $fechaFin, $tipo ){
        $empresas = $this->rawEmpresas;
        $labels = EstatusSolicitud::labels();
        $solicitudes = SolicitudesCompra::with([
            'DestinoVehiculo', 'SistemaMantenimiento', 'TipoMantenimiento',
            'Cotizaciones.orden_compra',
            'DetallesSolicitud.DetalleAutotanque.DatosVehiculo',
            'DetallesSolicitud.unidadMedida',
            'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
        ])
        ->where('estatus','>', EstatusSolicitud::AUTORIZADO_A_PAGO)
        ->where('activo', 1)
        ->where('tipo', $tipo)
        ->where('empresa', $empresa)
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->whereHas('DetallesSolicitud', function($q) {
            $q->where('confirmado', 1);
        })
        ->whereHas('Cotizaciones.orden_compra', function ($q) {
            $q->where('pagado', 1);
        })
        ->get()
        ->flatMap(function ($solicitud) use ($empresas, $labels) {


            //   OBTENER LA COTIZACIÓN QUE TIENE UNA ORDEN DE COMPRA
            $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
            $folioOC = $cotizacionOC->orden_compra->folio_oc ?? '';

            $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';

            $rows = [];
            $subtotal = 0;


            //   CALCULAR SUBTOTAL
            foreach ($solicitud->DetallesSolicitud as $detalle) {

                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );

                $precio = (float) ($cotSel->importe_unitario ?? 0);
                $cantidad = $detalle->cantidad ?? 1;

                $subtotal += $precio * $cantidad;
            }

            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;
            $proveedor = $cotSel->CotizacionesProveedores->datos_proveedor->nombre ?? 'N/A';

            $destinoSolicitud = $solicitud->DestinoVehiculo->nro_economico ?? null;
            $destinoFormat = ($solicitud->tipo == 2 && $destinoSolicitud)
                ? "ECO: $destinoSolicitud"
                : 'N/A';
            $tipoMantenimiento =  $solicitud->TipoMantenimiento->nombre ?? '';
            $sistemaMantenimiento =  $solicitud->SistemaMantenimiento->sistema ?? '';

            //   FILA PRINCIPAL: TOTALES DE LA SOLICITUD
            $rows[] = [
                'Folio'         => $solicitud->folio,
                'Folio_OC'      => $folioOC,
                'Fecha'         => date('d/m/Y H:i', strtotime($solicitud->fecha)),
                'Empresa'       => $empresas[$solicitud->empresa] ?? 'N/A',
                'Destino'       => $destinoFormat, 
                'Estado'        => $label,
                'Cantidad'      => '',
                'Descripcion'   => 'TOTALES DE LA SOLICITUD',
                'Observaciones' => '',
                'Unidad'        => '',
                'Proveedor'     => $proveedor,
                'Precio'        => '',
                'Subtotal'      => $subtotal,
                'IVA'           => $iva,
                'Total'         => $total,
                'tipoMantenimiento'         => $tipoMantenimiento,
                'sistemaMantenieminto'         => $sistemaMantenimiento,
            ];

            //   FILAS DETALLE DE LA SOLICITUD
            foreach ($solicitud->DetallesSolicitud as $detalle) {

                $cotSel = $detalle->DetallesCotizacion
                    ->firstWhere(fn($cot) =>
                        $cot->CotizacionesProveedores &&
                        $cot->CotizacionesProveedores->seleccionado == 1
                    );
    
                $precio = (float) ($cotSel->importe_unitario ?? 0);

                $ecoDetalle = $detalle->DetalleAutotanque->DatosVehiculo->nro_economico ?? null;
                $ecoDetalleFormat = ($solicitud->tipo == 2 && $ecoDetalle)
                    ? "ECO: $ecoDetalle"
                    : 'N/A';
                
                $rows[] = [
                    'Folio'         => '',
                    'Folio_OC'      => $folioOC,
                    'Fecha'         => '',
                    'Empresa'       => '',
                    'Destino'       => $ecoDetalleFormat,
                    'Estado'        => '',
                    'Cantidad'      => $detalle->cantidad ?? 0,
                    'Descripcion'   => $detalle->descripcion ?? '',
                    'Observaciones' => $detalle->observaciones ?? '',
                    'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                    'Proveedor'     => '',
                    'Precio'        => $precio,

                    'Subtotal'      => '',
                    'IVA'           => '',
                    'Total'         => '',
                    'tipoMantenimiento'  => '',
                    'sistemaMantenieminto' => '',
                ];
            }

            return $rows;
        });

        return $solicitudes;
    }

    /**
     * Recupera usuarios de la intranet para mostrarlo en el ereporte
     */
    public function getUsuario($id){
        if(isset($this->usuariosCache[$id])){
            return $this->usuariosCache[$id];
        }

        $data = DB::connection('intranet')
            ->select("call SOPORTEZM.SP_GetUsuarioId(?)", [$id]);

        $result = $data
            ? ['nombre' => $data[0]->firstname.' '.$data[0]->realname, 'area' => $data[0]->area]
            : ['nombre' => 'ND', 'area' => 'ND'];

        return $this->usuariosCache[$id] = $result;
    }



public function queryComprasDocumentos($empresa, $tipo = null)
{
    $empresas = $this->rawEmpresas;
    $labels   = EstatusSolicitud::labels();
    $allRows  = [];

    SolicitudesCompra::with([
            'Cotizaciones.orden_compra.documentos.documentosFactura',
            'Cotizaciones.orden_compra.acusesEntrega',
            'DetallesSolicitud.unidadMedida',
            'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor',
        ])
        ->where('estatus', '>', EstatusSolicitud::EN_ORDEN_COMPRA)
        ->where('estatus', '<>', EstatusSolicitud::CANCELADA)
        ->where('activo', 1)
        ->where('tipo', $tipo)
        ->where('empresa', $empresa)
        ->whereHas('DetallesSolicitud', fn($q) => $q->where('confirmado', 1))
        ->chunk(200, function ($solicitudes) use (&$allRows, $empresas, $labels) {

            foreach ($solicitudes as $solicitud) {
                $label          = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';
                $empresaNombre  = $empresas[$solicitud->empresa] ?? 'N/A';
                $fechaFormateada = $solicitud->fecha
                    ? date('d/m/Y H:i', strtotime($solicitud->fecha))
                    : '';

                $cotizacionOC = $solicitud->Cotizaciones->firstWhere('orden_compra', '!=', null);
                $ordenCompra  = $cotizacionOC?->orden_compra;
                $folioOC      = $ordenCompra->folio_oc ?? '';
                $fechaEntregaFormateada = $ordenCompra->fecha_entrega
                ? date('d/m/Y H:i', strtotime($ordenCompra->fecha_entrega))
                    : '';

                /*
                |----------------------------------------------------------
                | Documentos: facturas, comprobantes, complementos
                |----------------------------------------------------------
                */
                $documentos = $ordenCompra?->documentos ?? collect();

                $tieneFacturas = false;
                $tieneComprobantes = false;
                $tieneComplementos = false;

                foreach ($documentos as $doc) {

                    if (!$tieneFacturas && !empty($doc->ruta_xml_factura)) {
                        $tieneFacturas = true;
                    }

                    if (!$tieneComprobantes) {
                        $comprobanteDirecto = !empty($doc->comprobante_pago);
                        $comprobanteFactura = $doc->facturas?->contains(
                            fn($f) => $f->tipo_documento === 'comprobante_pago'
                                   && !empty($f->representacion_impresa)
                        );
                        if ($comprobanteDirecto || $comprobanteFactura) {
                            $tieneComprobantes = true;
                        }
                    }

                    if (!$tieneComplementos) {
                        $tieneComplementos = (bool) $doc->facturas?->contains(
                            fn($f) => $f->tipo_documento === 'complemento_pago'
                                   && !empty($f->xml)
                        );
                    }

                    // Si ya encontramos todo, no seguimos iterando
                    if ($tieneFacturas && $tieneComprobantes && $tieneComplementos) {
                        break;
                    }
                }

                $tieneAcuseEntrega = $ordenCompra?->acusesEntrega?->isNotEmpty() ? '1' : '0';


                // Proveedor seleccionado
                $proveedor = 'N/A';

                foreach ($solicitud->DetallesSolicitud as $detalle) {
                    if ($proveedor === 'N/A') {
                        $cotSel = $detalle->DetallesCotizacion
                            ->firstWhere(fn($cot) =>
                                $cot->CotizacionesProveedores &&
                                $cot->CotizacionesProveedores->seleccionado == 1
                            );
                        $nombreProveedor = $cotSel?->CotizacionesProveedores?->datos_proveedor->nombre ?? 'N/A';
                        if ($nombreProveedor !== 'N/A') {
                            $proveedor = $nombreProveedor;
                        }
                    }
                }

                $baseRow = [
                    'Folio'                 => $solicitud->folio,
                    'Folio_OC'              => $folioOC,
                    'Fecha'                 => $fechaFormateada,
                    'Empresa'               => $empresaNombre,
                    'Estado'                => $label,
                    'proveedor'             => $proveedor,
                    'FechaEntregaPrometida' => $fechaEntregaFormateada,
                    'FechaEntregaReal'      => '',
                    'TieneAcuseEntrega'     => $tieneAcuseEntrega,
                    'TieneFacturas'         => $tieneFacturas  ? '1' : '0',
                    'TieneComplementos'     => $tieneComplementos ? '1' : '0',
                    'TieneComprobantes'     => $tieneComprobantes ? '1' : '0',
                ];

                foreach ($solicitud->DetallesSolicitud as $detalle) {
                    $allRows[] = $baseRow + [
                        'Cantidad'    => $detalle->cantidad    ?? 0,
                        'Unidad'      => $detalle->unidadMedida->nombre ?? '',
                        'Descripcion' => $detalle->descripcion ?? '',
                    ];
                }

                // Liberar relaciones del modelo para que el GC las recoja
                $solicitud->unsetRelation('Cotizaciones');
                $solicitud->unsetRelation('DetallesSolicitud');
            }
        });

    return collect($allRows);
}
}