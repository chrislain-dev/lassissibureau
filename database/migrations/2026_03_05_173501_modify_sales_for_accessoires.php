<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modifie la table sales pour supporter les ventes d'accessoires :
     * - product_id devient nullable (un accessoire n'a pas d'unité individuelle)
     * - product_model_id ajouté pour lier une vente accessoire au modèle
     * - quantity_vendue ajouté pour indiquer combien d'unités ont été vendues
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Rendre product_id nullable (les ventes d'accessoires n'ont pas de Product individuel)
            $table->foreignId('product_id')->nullable()->change();

            // Lier la vente à un ProductModel (pour les accessoires)
            $table->foreignId('product_model_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_models')
                ->nullOnDelete()
                ->comment('Lien vers le modèle (accessoires uniquement, product_id sera null).');

            // Quantité vendue (1 par défaut pour les téléphones)
            $table->unsignedInteger('quantity_vendue')->default(1)->after('product_model_id')
                ->comment('Nombre d\'unités vendues. Toujours 1 pour les téléphones/tablettes.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_model_id');
            $table->dropColumn('quantity_vendue');
            // product_id redevient NOT NULL
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
