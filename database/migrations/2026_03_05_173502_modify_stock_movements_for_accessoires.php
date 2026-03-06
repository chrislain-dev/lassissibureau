<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modifie stock_movements pour supporter les mouvements d'accessoires :
     * - product_id devient nullable (les accessoires n'ont pas d'unité individuelle)
     * - product_model_id ajouté pour tracer les mouvements au niveau du modèle
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Rendre product_id nullable
            $table->foreignId('product_id')->nullable()->change();

            // Ajouter product_model_id pour les accessoires
            $table->foreignId('product_model_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_models')
                ->nullOnDelete()
                ->comment('Lien vers le modèle (accessoires uniquement, product_id sera null).');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_model_id');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
