<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FacturasExport implements Export, FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $companias;
    protected $fechaInicial;
    protected $fechaFinal;

    public function __construct(array $companias, string $fechaInicial, string $fechaFinal)
    {
        $this->companias = $companias;
        // Formato limpio sin milisegundos excesivos
        $this->fechaInicial = $fechaInicial . ' 00:00:00';
        $this->fechaFinal   = $fechaFinal . ' 23:59:59';
    }

    public function collection(): Collection
    {
        $resultados = collect();

        foreach ($this->companias as $companiaId) {
            // Se selecciona la conexión configurada en config/database.php según la compañía
            $connectionName = $this->getConnectionName($companiaId);

            if (!$connectionName) {
                continue;
            }

            // Ejecutamos el script T-SQL
            $rows = DB::connection($connectionName)->select(

    "
    SET NOCOUNT ON;
    IF OBJECT_ID('#FACTURAS', 'U') IS NOT NULL DROP TABLE #FACTURAS

		IF OBJECT_ID('#FORMASPAGO', 'U') IS NOT NULL
		 DROP TABLE #FORMASPAGO

		 DECLARE @F1 DATETIME, @F2 DATETIME
		SET @F1 = '{$this->fechaInicial}';
        SET @F2 = '{$this->fechaFinal}';

		SELECT
			IDENTITY(INT, 1, 1) RENGLON,
			CONVERT(VARCHAR(25), NULL) TIPODOCUMENTO,
			CONVERT(VARCHAR(4), NULL) CARTERA,
			CONVERT(INT, NULL) AGENCIA,
			CONVERT(VARCHAR(20), NULL) NOMBREAGENCIA,
			CONVERT(VARCHAR(20), NULL) FACTURA,
			CONVERT(DATETIME, NULL) FECHAFACTURA,
			CONVERT(DATETIME, NULL) FECHATIMBRADO,
			CONVERT(INT, NULL) FOLIO,
			CONVERT(VARCHAR(250), NULL) RAZONSOCIAL,
			CONVERT(VARCHAR(20), NULL) RFC,
			CONVERT(NUMERIC(18, 2), NULL) ISAN,
			CONVERT(NUMERIC(18, 2), NULL) IVA,
			CONVERT(NUMERIC(18, 2), NULL) SUBTOTAL,
			CONVERT(NUMERIC(18, 2), NULL) TOTAL,
			CONVERT(VARCHAR(10), NULL) CONDICIONDEPAGO,
			CONVERT(VARCHAR(10), NULL) TIPODEVENTA,
			CONVERT(VARCHAR(3), NULL) METODODEPAGO,
			CONVERT(VARCHAR(MAX), NULL) FORMADEPAGO,
			CONVERT(NUMERIC(18, 2), NULL) SALDO,
			CONVERT(VARCHAR(10), NULL) TIPOCOMPROBANTE,
			CONVERT(VARCHAR(20), NULL) POLIZA,
			CONVERT(VARCHAR(40), NULL) UUID,
			CONVERT(VARCHAR(20), NULL) POLIZACANC,
			CONVERT(DATETIME, NULL) FECHACANCELACION,
			CONVERT(VARCHAR(2), NULL) ESTATUS,
			CONVERT(DATETIME, NULL) FECHACANCELA,
			CONVERT(INT, NULL) CANCELADO,
			CONVERT(VARCHAR(2), NULL) ESTATUSCFDI,
			CONVERT(VARCHAR(20), NULL) INVENTARIO,
			CONVERT(VARCHAR(20), NULL) SERIE,
			CONVERT(VARCHAR(200), NULL) DESCRIPCIONMODELO,
			CONVERT(INT, NULL) ANIODELMODELO
		INTO #FACTURAS

		DELETE FROM #FACTURAS

		SELECT
			fofa_Prefijo + CONVERT(VARCHAR, fofa_Folio) FofaFolio,
			SUBSTRING(
						CAST(
								(
									SELECT
										' / ' +
										FormaPago
									FROM	(
												SELECT
													fofa_Prefijo PRE,
													fofa_Folio FOL,
													RTRIM(LTRIM(fofa_FPag_Clave + ' - ' + fofa_SatFPag_Descripcion)) + ': ' + CONVERT(VARCHAR, CONVERT(NUMERIC(18, 2), fofa_MontoPago)) FormaPago
												FROM Ca_FormaPagoXFactura
											) E2
									WHERE fofa_Prefijo = PRE AND fofa_Folio = FOL
									FOR XML PATH(''), TYPE
								) AS NVARCHAR(MAX)
							), 4, 99999
					) FofaFormaPago
		INTO #FORMASPAGO
		FROM Ca_FormaPagoXFactura

		INSERT INTO #FACTURAS	(TIPODOCUMENTO, CARTERA, AGENCIA, NOMBREAGENCIA, FACTURA, FECHAFACTURA, FECHATIMBRADO, FOLIO, RAZONSOCIAL, RFC, ISAN, IVA, SUBTOTAL, TOTAL, CONDICIONDEPAGO, TIPODEVENTA, METODODEPAGO, FORMADEPAGO, SALDO, TIPOCOMPROBANTE, POLIZA, UUID, POLIZACANC, FECHACANCELACION, ESTATUS, FECHACANCELA, CANCELADO, ESTATUSCFDI, INVENTARIO, SERIE, DESCRIPCIONMODELO, ANIODELMODELO)
		SELECT
			'FACTURA AUTOS' TIPODOCUMENTO,
			'AUTO' CARTERA,
			faau_idagencia AGENCIA,
			agen_nomagencia NOMBREAGENCIA,
			faau_nofactura FACTURA,
			faau_poli_fecha FECHAFACTURA,
			NULL FECHATIMBRADO,
			cont_folio FOLIO,
			faau_razonfactura RAZONSOCIAL,
			faau_rfcfactura RFC,
			faau_isan ISAN,
			faau_iva IVA,
			faau_total - faau_iva SUBTOTAL,
			faau_total TOTAL,
			CASE WHEN faau_formapago = 'CO' THEN 'CONTADO' ELSE 'CREDITO' END CONDICIONDEPAGO,
			faau_form_TipoVenta TIPODEVENTA,
			NULL METODODEPAGO,
			NULL FORMADEPAGO,
			NULL SALDO,
			'INGRESO' TIPOCOMPROBANTE,
			faau_poli_tipoclave + ' ' + CONVERT(VARCHAR, faau_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(faau_poli_fecha)) POLIZA,
			faau_UUID_CFDI UUID,
			pedi_tipo_clavecanc + ' ' + CONVERT(VARCHAR, pedi_poli_foliocanc) + ' ' + CONVERT(VARCHAR, YEAR(pedi_poli_fechacanc)) POLIZACANC,
			pedi_poli_fechacanc FECHACANCELACION,
			faau_status ESTATUS,
			NULL FECHACANCELA,
			NULL CANCELADO,
			NULL ESTATUSCFDI,
			vehi_clas_clave + ' ' + CONVERT(VARCHAR, vehi_anio) + ' ' + CONVERT(VARCHAR, vehi_numeroinventario) INVENTARIO,
			vehi_serie SERIE,
			mode_clave + ' - ' + mode_tipo + ' / ' + mode_descripcion DESCRIPCIONMODELO,
			vehi_anio_modelo ANIODELMODELO
		FROM Vt_FacturaAutos
		INNER JOIN Vt_InventarioAutos ON faau_vehi_vehiculoid = vehi_vehiculoid
		INNER JOIN Vt_Modelos ON vehi_mode_modeloid = mode_modeloid
		INNER JOIN Sg_Agencia ON faau_idagencia = agen_idagencia
		INNER JOIN Contactos ON faau_cont_clave = cont_clave
		WHERE faau_poli_fecha BETWEEN @F1 AND @F2
		ORDER BY faau_poli_fecha

		INSERT INTO #FACTURAS	(TIPODOCUMENTO, CARTERA, AGENCIA, NOMBREAGENCIA, FACTURA, FECHAFACTURA, FECHATIMBRADO, FOLIO, RAZONSOCIAL, RFC, ISAN, IVA, SUBTOTAL, TOTAL, CONDICIONDEPAGO, TIPODEVENTA, METODODEPAGO, FORMADEPAGO, SALDO, TIPOCOMPROBANTE, POLIZA, UUID, POLIZACANC, FECHACANCELACION, ESTATUS, FECHACANCELA, CANCELADO, ESTATUSCFDI, INVENTARIO, SERIE, DESCRIPCIONMODELO, ANIODELMODELO)
		SELECT
			'FACTURA REFACCIONES' TIPODOCUMENTO,
			'REFA' CARTERA,
			AG.agen_idagencia AGENCIA,
			AG.agen_nomagencia NOMBREAGENCIA,
			pedi_numerofactura FACTURA,
			pedi_poli_fecha FECHAFACTURA,
			NULL FECHATIMBRADO,
			cont_folio FOLIO,
			pedi_razonfactura RAZONSOCIAL,
			pedi_rfcfactura RFC,
			NULL ISAN,
			ISNULL(pedi_ivafactura, 0) IVA,
			ISNULL(pedi_importetotal, 0) + ISNULL(pedi_costoflete, 0) - ISNULL(pedi_Descuento, 0) SUBTOTAL,
			ISNULL(pedi_importetotal, 0) + ISNULL(pedi_costoflete, 0) - ISNULL(pedi_Descuento, 0) + ISNULL(pedi_ivafactura, 0) TOTAL,
			CASE WHEN pedi_tipoventa = 'CO' THEN 'CONTADO' ELSE 'CREDITO' END CONDICIONDEPAGO,
			NULL TIPODEVENTA,
			NULL METODODEPAGO,
			NULL FORMADEPAGO,
			NULL SALDO,
			'INGRESO' TIPOCOMPROBANTE,
			pedi_tipo_clave + ' ' + CONVERT(VARCHAR, pedi_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(pedi_poli_fecha)) POLIZA,
			pedi_UUID_CFDI UUID,
			pedi_tipo_clavecanc + ' ' + CONVERT(VARCHAR, pedi_poli_foliocanc) + ' ' + CONVERT(VARCHAR, YEAR(pedi_poli_fechacanc)) POLIZACANC,
			pedi_poli_fechacanc FECHACANCELACION,
			pedi_estatusfactura ESTATUS,
			NULL FECHACANCELA,
			NULL CANCELADO,
			NULL ESTATUSCFDI,
			NULL INVENTARIO,
			NULL SERIE,
			NULL DESCRIPCIONMODELO,
			NULL ANIODELMODELO
		FROM Re_EncabezadoPedido
		INNER JOIN Re_Almacenes AL ON pedi_alma_clave = alma_clave
		INNER JOIN Sg_Agencia AG ON AL.agen_idagencia = AG.agen_idagencia
		INNER JOIN Contactos ON pedi_cont_clave = cont_clave
		WHERE pedi_poli_fecha BETWEEN @F1 AND @F2
		ORDER BY pedi_poli_fecha

		INSERT INTO #FACTURAS	(TIPODOCUMENTO, CARTERA, AGENCIA, NOMBREAGENCIA, FACTURA, FECHAFACTURA, FECHATIMBRADO, FOLIO, RAZONSOCIAL, RFC, ISAN, IVA, SUBTOTAL, TOTAL, CONDICIONDEPAGO, TIPODEVENTA, METODODEPAGO, FORMADEPAGO, SALDO, TIPOCOMPROBANTE, POLIZA, UUID, POLIZACANC, FECHACANCELACION, ESTATUS, FECHACANCELA, CANCELADO, ESTATUSCFDI, INVENTARIO, SERIE, DESCRIPCIONMODELO, ANIODELMODELO)
		SELECT
			'FACTURA SERVICIO' TIPODOCUMENTO,
			'SERV' CARTERA,
			agen_idagencia AGENCIA,
			agen_nomagencia NOMBREAGENCIA,
			fase_folio FACTURA,
			fase_poli_fecha FECHAFACTURA,
			NULL FECHATIMBRADO,
			fase_cont_folio FOLIO,
			fase_razonsocial RAZONSOCIAL,
			fase_rfc RFC,
			NULL ISAN,
			ISNULL(fase_iva, 0) IVA,
			ISNULL(fase_subtotal, 0) SUBTOTAL,
			ISNULL(fase_total, 0) TOTAL,
			CASE WHEN fase_tipoventaid = 'CO' THEN 'CONTADO' ELSE 'CREDITO' END CONDICIONDEPAGO,
			NULL TIPODEVENTA,
			NULL METODODEPAGO,
			NULL FORMADEPAGO,
			NULL SALDO,
			'INGRESO' TIPOCOMPROBANTE,
			fase_tipo_clave + ' ' + CONVERT(VARCHAR, fase_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(fase_poli_fecha)) POLIZA,
			fase_UUID_CFDI UUID,
			fase_tipo_clave_canc + ' ' + CONVERT(VARCHAR, fase_poli_folio_canc) + ' ' + CONVERT(VARCHAR, YEAR(fase_poli_fecha_canc)) POLIZACANC,
			fase_poli_fecha_canc FECHACANCELACION,
			fase_status ESTATUS,
			NULL FECHACANCELA,
			NULL CANCELADO,
			NULL ESTATUSCFDI,
			NULL INVENTARIO,
			NULL SERIE,
			NULL DESCRIPCIONMODELO,
			NULL ANIODELMODELO
		FROM Se_FacturaServicios
		INNER JOIN Sg_Agencia ON fase_idagencia = agen_idagencia
		WHERE fase_poli_fecha BETWEEN @F1 AND @F2
		ORDER BY fase_poli_fecha

		INSERT INTO #FACTURAS	(TIPODOCUMENTO, CARTERA, AGENCIA, NOMBREAGENCIA, FACTURA, FECHAFACTURA, FECHATIMBRADO, FOLIO, RAZONSOCIAL, RFC, ISAN, IVA, SUBTOTAL, TOTAL, CONDICIONDEPAGO, TIPODEVENTA, METODODEPAGO, FORMADEPAGO, SALDO, TIPOCOMPROBANTE, POLIZA, UUID, POLIZACANC, FECHACANCELACION, ESTATUS, FECHACANCELA, CANCELADO, ESTATUSCFDI, INVENTARIO, SERIE, DESCRIPCIONMODELO, ANIODELMODELO)
		SELECT
			'FACTURA GENERAL' TIPODOCUMENTO,
			fact_cartera CARTERA,
			agen_idagencia AGENCIA,
			agen_nomagencia NOMBREAGENCIA,
			fact_folio FACTURA,
			fact_poli_fecha FECHAFACTURA,
			NULL FECHATIMBRADO,
			cont_folio FOLIO,
			fact_razonsocial RAZONSOCIAL,
			fact_rfc RFC,
			NULL ISAN,
			ISNULL(fact_iva, 0) IVA,
			ISNULL(fact_subtotal, 0) SUBTOTAL,
			ISNULL(fact_total, 0) TOTAL,
			CASE WHEN fact_tipoventaid = 'CO' THEN 'CONTADO' ELSE 'CREDITO' END CONDICIONDEPAGO,
			NULL TIPODEVENTA,
			NULL METODODEPAGO,
			NULL FORMADEPAGO,
			NULL SALDO,
			'INGRESO' TIPOCOMPROBANTE,
			fact_tipo_clave + ' ' + CONVERT(VARCHAR, fact_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(fact_poli_fecha)) POLIZA,
			fact_UUID UUID,
			fact_tipo_clave_canc + ' ' + CONVERT(VARCHAR, fact_poli_folio_canc) + ' ' + CONVERT(VARCHAR, YEAR(fact_poli_fecha_canc)) POLIZACANC,
			fact_poli_fecha_canc FECHACANCELACION,
			fact_status ESTATUS,
			NULL FECHACANCELA,
			NULL CANCELADO,
			NULL ESTATUSCFDI,
			NULL INVENTARIO,
			NULL SERIE,
			NULL DESCRIPCIONMODELO,
			NULL ANIODELMODELO
		FROM Fa_FacturasGeneral
		INNER JOIN Sg_Agencia ON fact_idagencia = agen_idagencia
		INNER JOIN Contactos ON fact_cont_clave = cont_clave
		WHERE fact_poli_fecha BETWEEN @F1 AND @F2
		ORDER BY fact_poli_fecha

		INSERT INTO #FACTURAS	(TIPODOCUMENTO, CARTERA, AGENCIA, NOMBREAGENCIA, FACTURA, FECHAFACTURA, FECHATIMBRADO, FOLIO, RAZONSOCIAL, RFC, ISAN, IVA, SUBTOTAL, TOTAL, CONDICIONDEPAGO, TIPODEVENTA, METODODEPAGO, FORMADEPAGO, SALDO, TIPOCOMPROBANTE, POLIZA, UUID, POLIZACANC, FECHACANCELACION, ESTATUS, FECHACANCELA, CANCELADO, ESTATUSCFDI, INVENTARIO, SERIE, DESCRIPCIONMODELO, ANIODELMODELO)
		SELECT
			'NOTAS DE CARGO' TIPODOCUMENTO,
			prim_cartera CARTERA,
			agen_idagencia AGENCIA,
			agen_nomagencia NOMBREAGENCIA,
			prim_documento FACTURA,
			prim_poli_fecha FECHAFACTURA,
			NULL FECHATIMBRADO,
			cont_folio FOLIO,
			ISNULL(cont_razonsocial, cont_nombre + ' ' + cont_apellidopaterno + ' ' + cont_apellidomaterno) RAZONSOCIAL,
			cont_rfc RFC,
			NULL ISAN,
			CONVERT(NUMERIC(18, 2), prim_importe / (1 + (IVA / 100))) IVA,
			prim_importe - CONVERT(NUMERIC(18, 2), prim_importe / (1 + (IVA / 100))) SUBTOTAL,
			prim_importe TOTAL,
			CASE WHEN prim_formaventa = 'CO' THEN 'CONTADO' ELSE 'CREDITO' END CONDICIONDEPAGO,
			prim_tipoventa TIPODEVENTA,
			NULL METODODEPAGO,
			NULL FORMADEPAGO,
			NULL SALDO,
			'INGRESO' TIPOCOMPROBANTE,
			prim_tipo_clave + ' ' + CONVERT(VARCHAR, prim_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(prim_poli_fecha)) POLIZA,
			prim_UUID_CFDI UUID,
			prim_Tipo_ClaveCanc + ' ' + CONVERT(VARCHAR, prim_Poli_FolioCanc) + ' ' + CONVERT(VARCHAR, YEAR(prim_Poli_FechaCanc)) POLIZACANC,
			prim_Poli_FechaCanc FECHACANCELACION,
			CASE WHEN ISNULL(prim_Poli_FechaCanc, 0) > 0 THEN 'CA' ELSE 'AC' END ESTATUS,
			NULL FECHACANCELA,
			NULL CANCELADO,
			NULL ESTATUSCFDI,
			NULL INVENTARIO,
			NULL SERIE,
			NULL DESCRIPCIONMODELO,
			NULL ANIODELMODELO
		FROM Cc_Primclientes
		INNER JOIN Sg_Agencia ON prim_referencia3 = agen_nomagencia
		INNER JOIN Contactos ON prim_cont_clave = cont_clave
		INNER JOIN Ct_IVAs ON prim_poli_fecha BETWEEN FID AND FFD
		WHERE prim_poli_fecha BETWEEN @F1 AND @F2 AND prim_tipo = 'NCAR'
		ORDER BY prim_poli_fecha

		UPDATE #FACTURAS
		SET FECHATIMBRADO = FaelFecha,
			METODODEPAGO = FaelMetodo,
			FECHACANCELA = FaelFechaCancela,
			CANCELADO = FaelCancelado,
			ESTATUSCFDI = FaelEstatus
		FROM	(
					SELECT
						fael_serie + CONVERT(VARCHAR, fael_folio) FaelFolio,
						CASE WHEN ISNULL(CHARINDEX('T', fael_FechaTimbrado), 0) = 0
							THEN CONVERT(DATETIME, SUBSTRING(fael_FechaTimbrado, 1, 10), 103) + CONVERT(DATETIME, REPLACE(REPLACE(SUBSTRING(fael_FechaTimbrado, 12, 20), '.', ''), ' ', ''), 9)
							ELSE CONVERT(DATETIME, SUBSTRING(fael_FechaTimbrado, 1, 19))
						END FaelFecha,
						fael_metodoPago FaelMetodo,
						fael_fechacancela FaelFechaCancela,
						fael_cancelado FaelCancelado,
						fael_Status FaelEstatus
					FROM fa_FacturaElectronica
				) FaEl
		WHERE FACTURA = FaelFolio

		UPDATE #FACTURAS
		SET FORMADEPAGO = FofaFormaPago
		FROM #FORMASPAGO
		WHERE FACTURA = FofaFolio

		UPDATE #FACTURAS
		SET SALDO = PrimSaldo
		FROM	(
					SELECT
						prim_documento PrimDocto,
						prim_tipo_clave + ' ' + CONVERT(VARCHAR, prim_poli_folio) + ' ' + CONVERT(VARCHAR, YEAR(prim_poli_fecha)) PrimPoliza,
						SUM(prim_saldo) PrimSaldo
					FROM Cc_Primclientes
					GROUP BY prim_documento, prim_tipo_clave, prim_poli_folio, YEAR(prim_poli_fecha)
				) PRIM
		WHERE FACTURA = PrimDocto AND POLIZA = PrimPoliza

		SELECT
		RENGLON,
		TIPODOCUMENTO,
		CARTERA,
		AGENCIA,
		NOMBREAGENCIA,
		FACTURA,
		FECHAFACTURA,
		FECHATIMBRADO,
		FOLIO,
		RAZONSOCIAL,
		RFC,
		ISAN,
		IVA,
		SUBTOTAL,
		TOTAL,
		CONDICIONDEPAGO,
		TIPODEVENTA,
		METODODEPAGO,
		SALDO,
		TIPOCOMPROBANTE,
		POLIZA,
		UUID,
		POLIZACANC,
		FECHACANCELACION,
		ESTATUS,
		FECHACANCELA,
		CANCELADO,
		ESTATUSCFDI,
		INVENTARIO,
		SERIE,
		DESCRIPCIONMODELO,
		ANIODELMODELO
		FROM #FACTURAS
		WHERE METODODEPAGO = 'PUE' AND
		SALDO > 0
		ORDER BY RENGLON");

            $resultados = $resultados->concat($rows);
        }
        return $resultados;
    }

    public function headings(): array
    {
        return [
            'Renglón', 'Tipo Documento', 'Cartera', 'Agencia', 'Nombre Agencia',
            'Factura', 'Fecha Factura', 'Fecha Timbrado', 'Folio', 'Razón Social',
            'RFC', 'ISAN', 'IVA', 'Subtotal', 'Total', 'Condición Pago',
            'Tipo Venta', 'Método Pago', 'Saldo', 'Tipo Comprobante', 'Póliza',
            'UUID', 'Póliza Canc.', 'Fecha Canc.', 'Estatus', 'Fecha Cancela',
            'Cancelado', 'Estatus CFDI', 'Inventario', 'Serie', 'Descripción', 'Año Modelo'
        ];
    }

    public function map($row): array
    {
        return [
            $row->RENGLON,
            $row->TIPODOCUMENTO,
            $row->CARTERA,
            $row->AGENCIA,
            $row->NOMBREAGENCIA,
            $row->FACTURA,
            $row->FECHAFACTURA,
            $row->FECHATIMBRADO,
            $row->FOLIO,
            $row->RAZONSOCIAL,
            $row->RFC,
            $row->ISAN,
            $row->IVA,
            $row->SUBTOTAL,
            $row->TOTAL,
            $row->CONDICIONDEPAGO,
            $row->TIPODEVENTA,
            $row->METODODEPAGO,
            $row->SALDO,
            $row->TIPOCOMPROBANTE,
            $row->POLIZA,
            $row->UUID,
            $row->POLIZACANC,
            $row->FECHACANCELACION,
            $row->ESTATUS,
            $row->FECHACANCELA,
            $row->CANCELADO,
            $row->ESTATUSCFDI,
            $row->INVENTARIO,
            $row->SERIE,
            $row->DESCRIPCIONMODELO,
            $row->ANIODELMODELO,
        ];
    }

    private function getConnectionName($id)
    {
        return match ((int)$id) {
            706 => 'renault',
            // 707 => 'nissan_insurgentes',
            705 => 'nissan_azcapotzalco',
            709 => 'nissan_campestre',
            710 => 'nissan_universidad',
            default => null,
        };
    }
}
