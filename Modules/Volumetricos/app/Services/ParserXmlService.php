<?php

namespace Modules\Volumetricos\Services;

use App\Imports\VolumetricosImport;
use Maatwebsite\Excel\Facades\Excel;
use DOMDocument;
use DOMElement;

class ParserXmlService
{
    /**
     * TODO: Reemplazar por las URIs oficiales del SAT cuando se tengan.
     * Por ahora se usan placeholders para poder generar un XML bien formado.
     */
    // private const NS_COVOL = 'http://www.sat.gob.mx/controlvolumetrico';
    // private const NS_PREFIX_BASE = 'http://www.sat.gob.mx/controlvolumetrico/';

    private const NS_COVOL = '';
    private const NS_PREFIX_BASE = '';

    /** @var array<string,string> prefijo => URI, se registran conforme se van usando */
    private array $namespacesUsados = [];

    /** @var DOMElement referencia al nodo raíz para declarar los xmlns conforme aparecen */
    private DOMElement $rootElement;

    public function generateXml($file): string
    {
        $import = new VolumetricosImport();
        Excel::import($import, $file);

        // 1. Extraemos los datos base (igual que en el parser de JSON)
        $general        = $import->generalImport->data;
        $permisos       = $import->permisosImport->data;
        $controlGeneral = $import->controlGeneralImport->data;

        $recepcionesConsolidadas = $import->getTodasLasRecepciones();
        $entregasConsolidadas    = $import->getTodasLasEntregas();

        $existencias = (float)$controlGeneral['ExistenciasMesInmediatoAnterior'];

        // 2. Preparamos el documento y la raíz
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $this->namespacesUsados['Covol'] = self::NS_COVOL;

        $root = $doc->createElement('Covol:Reporte');
        $doc->appendChild($root);
        $this->rootElement = $root;
        // Declaramos Covol de una vez; los demás prefijos (exo, dis, etc.) se
        // van agregando dinámicamente en registrarNamespace() según se usan.
        // $root->setAttribute('xmlns:Covol', self::NS_COVOL);

        // 3. Construimos los nodos generales (equivalentes a los campos sueltos del JSON)
        $this->addChild($doc, $root, 'Covol', 'Version', '1.0');
        $this->addChild($doc, $root, 'Covol', 'RfcContribuyente', $general['RfcContribuyente'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'RfcRepresentanteLegal', $general['RfcRepresentanteLegal'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'RfcProveedor', $general['RfcProveedor'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'Caracter', $permisos['Caracter'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'ModalidadPermiso', $permisos['ModalidadPermiso'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'NumPermiso', $permisos['NumPermiso'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'ClaveInstalacion', $permisos['ClaveInstalacion'] ?? '');
        $this->addChild($doc, $root, 'Covol', 'DescripcionInstalacion', $permisos['DescripcionInstalacion'] ?? '');

        // El JSON envolvía Geolocalizacion en un array de 1 elemento -> aquí se representa
        // como un nodo contenedor con un único hijo "Punto". Ajustar si el formato real difiere.
        // $geoEl = $this->addChild($doc, $root, 'Covol', 'Geolocalizacion');
        // $this->addChild($doc, $geoEl, 'Covol', 'Punto', (string)($permisos['Geolocalizacion'] ?? ''));

        $this->addChild($doc, $root, 'Covol', 'NumeroPozos', (string)($permisos['NumeroPozos'] ?? 0));
        $this->addChild($doc, $root, 'Covol', 'NumeroTanques', (string)($permisos['NumeroTanques'] ?? 0));
        $this->addChild($doc, $root, 'Covol', 'NumeroDuctosEntradaSalida', (string)($permisos['NumeroDuctosEntradaSalida'] ?? 0));
        $this->addChild($doc, $root, 'Covol', 'NumeroDuctosTransporteDistribucion', (string)($permisos['NumeroDuctosTransporteDistribucion'] ?? 0));
        $this->addChild($doc, $root, 'Covol', 'NumeroDispensarios', (string)($permisos['NumeroDispensarios'] ?? 0));
        $this->addChild($doc, $root, 'Covol', 'FechaYHoraReporteMes', $permisos['FechaYHoraReporteMes'] ?? now()->toIso8601String());

        // 4. Nodo Producto
        $productoEl = $this->buildProductoNode(
            $doc,
            $permisos['ClaveProducto'],
            $existencias,
            $permisos['FechaYHoraReporteMes'],
            $recepcionesConsolidadas,
            $entregasConsolidadas,
            (float)$permisos['ComposDePropanoEnGasLP'],
            (float)$permisos['ComposDeButanoEnGasLP']
        );
        $root->appendChild($productoEl);

        // El JSON lo dejaba como array vacío; aquí se representa como nodo vacío
        $this->addChild($doc, $root, 'Covol', 'BitacoraMensual');

        return $doc->saveXML();
    }

    /**
     * Construye el nodo <Covol:Producto> con Recepciones/Entregas y sus Complementos.
     */
    private function buildProductoNode(
        DOMDocument $doc,
        string $claveProducto,
        float $volumenExistencias,
        string $fechaMedicion,
        array $filasRecepcionesExcel,
        array $filasEntregasExcel,
        float $propano,
        float $butano,
        string $unidadMedida = 'UM03'
    ): DOMElement {
        $productoEl = $doc->createElement('Covol:Producto');
        $this->addChild($doc, $productoEl, 'Covol', 'ClaveProducto', $claveProducto);

        $reporteEl = $this->addChild($doc, $productoEl, 'Covol', 'ReporteDeVolumenMensual');

        $colRecepciones = collect($filasRecepcionesExcel);
        $colEntregas    = collect($filasEntregasExcel);

        $recepcionesVol = round($colRecepciones->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4);
        $entregasVol    = round($colEntregas->sum(fn($i) => (float)($i['VolumenDocumentado'] ?? 0)), 4);

        $diferenciaPositiva = $recepcionesVol - $entregasVol;
        $volExistenciasMes  = round($volumenExistencias + $diferenciaPositiva, 4);

        // ControlDeExistencias
        $controlEl = $this->addChild($doc, $reporteEl, 'Covol', 'ControlDeExistencias');
        $this->addChild($doc, $controlEl, 'Covol', 'VolumenExistenciasMes', (string)$volExistenciasMes);
        $this->addChild($doc, $controlEl, 'Covol', 'FechaYHoraEstaMedicionMes', $fechaMedicion);

        // Recepciones
        $recepcionesEl = $this->addChild($doc, $reporteEl, 'Covol', 'Recepciones');
        $this->addChild($doc, $recepcionesEl, 'Covol', 'TotalRecepcionesMes', (string)$colRecepciones->count());

        $sumaVolRecEl = $this->addChild($doc, $recepcionesEl, 'Covol', 'SumaVolumenRecepcionMes');
        $this->addChild($doc, $sumaVolRecEl, 'Covol', 'ValorNumerico', (string)$recepcionesVol);
        $this->addChild($doc, $sumaVolRecEl, 'Covol', 'UnidadDeMedida', $unidadMedida);

        $this->addChild($doc, $recepcionesEl, 'Covol', 'TotalDocumentosMes', (string)$colRecepciones->count());
        $importeRecepciones = round($colRecepciones->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4);
        $this->addChild($doc, $recepcionesEl, 'Covol', 'ImporteTotalRecepcionesMensual', (string)$importeRecepciones);

        foreach ($colRecepciones as $item) {
            $recepcionesEl->appendChild($this->buildComplementoElement($doc, $item, $unidadMedida));
        }

        // Entregas
        $entregasEl = $this->addChild($doc, $reporteEl, 'Covol', 'Entregas');
        $this->addChild($doc, $entregasEl, 'Covol', 'TotalEntregasMes', (string)$colEntregas->count());

        $sumaVolEntEl = $this->addChild($doc, $entregasEl, 'Covol', 'SumaVolumenEntregadoMes');
        $this->addChild($doc, $sumaVolEntEl, 'Covol', 'ValorNumerico', (string)$entregasVol);
        $this->addChild($doc, $sumaVolEntEl, 'Covol', 'UnidadDeMedida', $unidadMedida);

        $this->addChild($doc, $entregasEl, 'Covol', 'TotalDocumentosMes', (string)$colEntregas->count());
        $importeEntregas = round($colEntregas->sum(fn($i) => (float)($i['PrecioVentaOCompraOContrap'] ?? 0)), 4);
        $this->addChild($doc, $entregasEl, 'Covol', 'ImporteTotalEntregasMes', (string)$importeEntregas);

        foreach ($colEntregas as $item) {
            $entregasEl->appendChild($this->buildComplementoElement($doc, $item, $unidadMedida));
        }

        $this->addChild($doc, $productoEl, 'Covol', 'ComposDePropanoEnGasLP', (string)$propano);
        $this->addChild($doc, $productoEl, 'Covol', 'ComposDeButanoEnGasLP', (string)$butano);

        return $productoEl;
    }

    /**
     * Construye <Covol:Complemento>, cuyos HIJOS usan el prefijo dinámico derivado
     * del prefijo de ClaveInstalacion en minúsculas (ej. "EXO-001" -> exo:, "DIS-045" -> dis:).
     * Misma lógica de TipoComplemento / CFDI que el parser de JSON.
     */
    private function buildComplementoElement(DOMDocument $doc, array $item, string $unidadMedida): DOMElement
    {
        $claveInstalacion = $item['ClaveInstalacion'] ?? '';
        $prefijoRaw = strtoupper(trim(strstr($claveInstalacion, '-', true) ?: $claveInstalacion));

        $prefijo = match (strtolower($prefijoRaw)) {
            'pdd' => 'dis',
            'exo' => 'exp',
            default => 'Covol'
        };
        // $prefijo = $prefijoRaw !== '' ? strtolower($prefijoRaw) : 'Covol';
        $tipoComplemento = ($prefijoRaw === 'EXO') ? 'Expendio' : 'Distribucion';

        // El contenedor <Complemento> se mantiene en Covol; sus hijos van con el prefijo dinámico
        $complementoEl = $doc->createElement('Covol:Complemento');

        $this->addChild($doc, $complementoEl, $prefijo, 'TipoComplemento', $tipoComplemento);

        $cfdi = trim((string)($item['CFDI'] ?? ''));
        $aclaracion = trim((string)($item['Aclaracion'] ?? ''));

        // CASO 1: Existe CFDI
        if ($cfdi !== '') {
            $nombre = trim((string)($item['NombreClienteOProveedor'] ?? ''));
            if (strtoupper($nombre) === 'VENTA AL PUBLICO EN GENERAL') {
                $nombre = 'Público en General';
            }

            $nacionalEl = $this->addChild($doc, $complementoEl, $prefijo, 'Nacional');
            $this->addChild($doc, $nacionalEl, $prefijo, 'RfcClienteOProveedor', (string)($item['RfcClienteOProveedor'] ?? ''));
            $this->addChild($doc, $nacionalEl, $prefijo, 'NombreClienteOProveedor', $nombre);

            $cfdisEl = $this->addChild($doc, $nacionalEl, $prefijo, 'CFDIs');
            $this->addChild($doc, $cfdisEl, $prefijo, 'CFDI', $cfdi);
            $this->addChild($doc, $cfdisEl, $prefijo, 'TipoCfdi', (string)($item['TipoCFDI'] ?? 'Ingreso'));
            $this->addChild($doc, $cfdisEl, $prefijo, 'PrecioVentaOCompraOContrap', (string)((float)($item['PrecioVentaOCompraOContrap'] ?? 0)));
            $this->addChild($doc, $cfdisEl, $prefijo, 'FechaYHoraTransaccion', (string)($item['FechaYHoraTransaccion'] ?? ''));

            $volEl = $this->addChild($doc, $cfdisEl, $prefijo, 'VolumenDocumentado');
            $this->addChild($doc, $volEl, $prefijo, 'ValorNumerico', (string)round((float)($item['VolumenDocumentado'] ?? 0), 4));
            $this->addChild($doc, $volEl, $prefijo, 'UnidadDeMedida', $unidadMedida);

            return $complementoEl;
        }

        // CASO 2: NO existe CFDI
        $textoAclaracion = $aclaracion
            . '. Volumen: '
            . $this->formatVolumen($item['VolumenDocumentado'] ?? 0)
            . ' Litros';

        $this->addChild($doc, $complementoEl, $prefijo, 'Aclaracion', $textoAclaracion);

        return $complementoEl;
    }

    /**
     * Crea un elemento con el namespace correspondiente al prefijo, registrando (y
     * declarando en la raíz) el namespace la primera vez que se usa ese prefijo.
     */
    private function addChild(DOMDocument $doc, DOMElement $parent, string $prefix, string $name, ?string $value = null): DOMElement
    {
        $this->registrarNamespace($prefix);

        $el = $doc->createElement($prefix . ':' . $name);
        if ($value !== null && $value !== '') {
            $el->appendChild($doc->createTextNode($this->cleanUtf8($value)));
        }
        $parent->appendChild($el);

        return $el;
    }

    /**
     * Registra el namespace de un prefijo (si es nuevo) y lo declara como
     * atributo xmlns:prefijo en la raíz para que quede centralizado ahí.
     */
    private function registrarNamespace(string $prefix): string
    {
        if (!isset($this->namespacesUsados[$prefix])) {
            // TODO: reemplazar por la URI oficial del SAT para este prefijo/catálogo
            $uri = $prefix === 'Covol' ? self::NS_COVOL : self::NS_PREFIX_BASE . $prefix;
            $this->namespacesUsados[$prefix] = $uri;

            if (isset($this->rootElement) && $prefix !== 'Covol') {
                // Se declara como atributo simple xmlns:prefijo="uri" en la raíz,
                // sin usar la API namespace-aware de DOM.
                $this->rootElement->setAttribute('xmlns:' . $prefix, $uri);
            }
        }

        return $this->namespacesUsados[$prefix];
    }

    private function cleanUtf8(string $value): string
    {
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    private function formatVolumen($valor): string
    {
        return rtrim(
            rtrim(number_format((float)$valor, 4, '.', ''), '0'),
            '.'
        );
    }
}
