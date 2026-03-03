<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Référence de transaction (numéro Mobile Money, référence virement, etc.)
            $table->string('reference')->nullable()->after('payment_date')
                ->comment('Référence de transaction : numéro Mobile Money, virement, etc.');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
