<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_models', function (Blueprint $table) {
            // Prix de vente spécifique aux revendeurs/partenaires
            $table->decimal('prix_vente_revendeur', 10, 2)->nullable()->after('prix_vente_default')
                ->comment('Prix de vente aux revendeurs/partenaires (différent du prix client)');

            // Condition commerciale du modèle : neuve ou occasion
            $table->string('condition_type')->default('neuve')->after('prix_vente_revendeur')
                ->comment('Condition commerciale : neuve ou occasion');
        });
    }

    public function down(): void
    {
        Schema::table('product_models', function (Blueprint $table) {
            $table->dropColumn(['prix_vente_revendeur', 'condition_type']);
        });
    }
};
