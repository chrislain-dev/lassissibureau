<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_ins', function (Blueprint $table) {
            // Indique si le téléphone reçu en troc nécessite une réparation
            $table->boolean('needs_repair')->default(false)->after('etat_recu')
                ->comment('Le téléphone reçu nécessite-t-il une réparation ?');

            $table->text('repair_notes')->nullable()->after('needs_repair')
                ->comment('Notes sur la réparation à effectuer');

            $table->string('repair_status')->nullable()->after('repair_notes')
                ->comment('Statut de la réparation : en_attente_reparation, en_reparation, repare');

            $table->index('needs_repair');
        });
    }

    public function down(): void
    {
        Schema::table('trade_ins', function (Blueprint $table) {
            $table->dropColumn(['needs_repair', 'repair_notes', 'repair_status']);
        });
    }
};
