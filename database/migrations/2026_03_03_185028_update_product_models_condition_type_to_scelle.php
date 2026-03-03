<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convertit les anciennes valeurs 'scelle' et 'neuve' → 'neuf', 'venue' → 'venu'
     */
    public function up(): void
    {
        // Convertir 'scelle' (ancienne valeur) → 'neuf'
        DB::table('product_models')
            ->where('condition_type', 'scelle')
            ->update(['condition_type' => 'neuf']);

        // Convertir 'neuve' (très ancienne valeur) → 'neuf'
        DB::table('product_models')
            ->where('condition_type', 'neuve')
            ->update(['condition_type' => 'neuf']);

        // Convertir 'venue' (ancienne valeur) → 'venu'
        DB::table('product_models')
            ->where('condition_type', 'venue')
            ->update(['condition_type' => 'venu']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('product_models')
            ->where('condition_type', 'neuf')
            ->update(['condition_type' => 'scelle']);

        DB::table('product_models')
            ->where('condition_type', 'venu')
            ->update(['condition_type' => 'venue']);
    }
};

