<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute les colonnes de gestion de stock par quantité pour les accessoires.
     * - quantity      : stock disponible (null = téléphone/tablette géré par unité)
     * - quantity_sold : nombre d'unités vendues (traçabilité)
     */
    public function up(): void
    {
        Schema::table('product_models', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->nullable()->default(null)->after('stock_minimum')
                ->comment('Stock en unités (accessoires). Null = produit géré par unité individuelle.');
            $table->unsignedInteger('quantity_sold')->default(0)->after('quantity')
                ->comment('Nombre total d\'unités vendues (accessoires).');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_models', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'quantity_sold']);
        });
    }
};
