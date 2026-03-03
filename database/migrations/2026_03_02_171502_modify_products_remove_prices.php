<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Supprimer les prix individuels — ils viennent désormais du ProductModel
            $table->dropColumn(['prix_achat', 'prix_vente']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('prix_achat', 10, 2)->nullable()->comment('Prix réel d\'achat de ce produit');
            $table->decimal('prix_vente', 10, 2)->nullable()->comment('Prix de vente actuel');
        });
    }
};
