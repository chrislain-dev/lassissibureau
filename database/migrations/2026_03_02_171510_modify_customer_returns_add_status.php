<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            // Statut du workflow de retour
            $table->string('status')->default('en_attente')->after('processed_by')
                ->comment('Statut : en_attente, en_reparation, retour_fournisseur, remplace, resolu, clos');

            // Notes de réparation
            $table->text('repair_notes')->nullable()->after('defect_description')
                ->comment('Notes concernant la réparation effectuée ou à effectuer');

            // Lien vers le retour fournisseur (si envoyé au fournisseur)
            $table->foreignId('supplier_return_id')->nullable()->after('status')
                ->constrained('supplier_returns')->nullOnDelete()
                ->comment('Retour fournisseur associé si le produit est renvoyé au fournisseur');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $table->dropForeign(['supplier_return_id']);
            $table->dropColumn(['status', 'repair_notes', 'supplier_return_id']);
        });
    }
};
