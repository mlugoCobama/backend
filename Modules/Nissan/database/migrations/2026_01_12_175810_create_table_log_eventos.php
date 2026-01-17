<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'autos';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // usuario que hizo la acción
            $table->string('table_name'); // nombre de la tabla afectada
            $table->unsignedBigInteger('record_id'); // id del registro afectado
            $table->string('event'); // tipo de evento: create, update, delete
            $table->json('old_values')->nullable(); // valores anteriores
            $table->json('new_values')->nullable(); // valores nuevos
            $table->string('ip_address')->nullable(); // IP del usuario
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_eventos');
    }


};
