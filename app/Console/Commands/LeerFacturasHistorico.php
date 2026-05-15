<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Services\CfdiService;

class LeerFacturasHistorico extends Command
{
    public function __construct(protected CfdiService $cfdiService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:leer-facturas-historico';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lee el contenido de tdas las factura y actualiza serie, folio, subtotal,total, rfc_emisor en la bd';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $docs = DocumentosOrdenesCompra::whereNotNull('ruta_xml_factura')
         ->where('ruta_xml_factura', '<>', '')
         ->get();

        foreach ($docs as $doc) {
            try {
                if (Storage::exists($doc->ruta_xml_factura)) {
                    // Parsear el XML
                    $factura = $this->cfdiService->parsearDesdeRuta(Storage::path($doc->ruta_xml_factura));

                    // Actualizar campos
                    $doc->serie      = $factura['serie'];
                    $doc->folio      = $factura['folio'];
                    $doc->subtotal   = $factura['subtotal'];
                    $doc->total      = $factura['total'];
                    $doc->emisor_rfc = $factura['emisor_rfc'];
                    $doc->save();
                }
            } catch (\Throwable $e) {
                // Registrar el error y continuar con el siguiente documento
                Log::error("Error al procesar documento ID {$doc->id}: ".$e->getMessage());
                continue;
            }
        }
    }
}
