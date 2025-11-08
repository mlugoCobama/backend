<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    /**
     * El nombre y la firma del comando (lo que escribes en consola).
     *
     * php artisan mail:test correo@ejemplo.com
     */
    protected $signature = 'mail:test {to}';

    /**
     * La descripción del comando.
     */
    protected $description = 'Envía un correo de prueba para validar configuración de correo';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $to = $this->argument('to');

        try {
            Mail::raw('Este es un correo de prueba enviado desde Laravel 🚀', function ($message) use ($to) {
                $message->to($to)
                        ->subject('Correo de prueba Laravel');
            });

            $this->info("✅ Correo enviado correctamente a: {$to}");
        } catch (\Exception $e) {
            $this->error("❌ Error enviando correo: " . $e->getMessage());
        }
    }
}
