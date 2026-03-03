<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();

            // Produit envoyé au fournisseur
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()
                ->comment('Produit envoyé au fournisseur');

            // Lien optionnel vers le retour client qui a déclenché ce retour fournisseur
            $table->foreignId('customer_return_id')->nullable()->constrained('customer_returns')->nullOnDelete()
                ->comment('Retour client à l\'origine de ce retour fournisseur');

            // Informations sur l'envoi
            $table->text('motif')->comment('Raison de l\'envoi au fournisseur');
            $table->date('date_envoi')->comment('Date d\'envoi au fournisseur');
            $table->date('date_retour_prevue')->nullable()->comment('Date de retour prévue');
            $table->date('date_retour_effective')->nullable()->comment('Date de retour effective');

            // Statut du retour fournisseur
            $table->string('statut')->default('en_attente')
                ->comment('Statut : en_attente, recu, remplace');

            // Produit de remplacement reçu du fournisseur
            $table->foreignId('replacement_product_id')->nullable()->constrained('products')->nullOnDelete()
                ->comment('Nouveau produit reçu en remplacement');

            $table->text('notes')->nullable();

            // Traçabilité
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete()
                ->comment('Utilisateur ayant créé ce retour fournisseur');

            $table->timestamps();

            // Index
            $table->index('statut');
            $table->index('date_envoi');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_returns');
    }
};
