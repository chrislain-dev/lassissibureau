<?php

namespace App\Enums;

/**
 * Type de mouvement de stock (historique)
 */
enum StockMovementType: string
{
    // Entrées
    case RECEPTION_FOURNISSEUR = 'reception_fournisseur';
    case TROC_RECU = 'troc_recu'; // Téléphone reçu en échange
    case RETOUR_REVENDEUR = 'retour_revendeur';
    case RETOUR_CLIENT = 'retour_client';
    case RETOUR_REPARATION = 'retour_reparation'; // Retour de réparation

    // Sorties
    case VENTE_DIRECTE = 'vente_directe';
    case VENTE_TROC = 'vente_troc'; // Vente avec troc
    case DEPOT_REVENDEUR = 'depot_revendeur';
    case ECHANGE_RETOUR = 'echange_retour'; // Échange suite retour client
    case ENVOI_REPARATION = 'envoi_reparation';
    case RETOUR_FOURNISSEUR = 'retour_fournisseur';

    // Annulations (remise en stock suite suppression de vente)
    case ANNULATION_VENTE = 'annulation_vente';

    // Pertes
    case CASSE = 'casse';
    case VOL = 'vol';
    case PERTE = 'perte';

    // Corrections
    case CORRECTION_PLUS = 'correction_plus';
    case CORRECTION_MOINS = 'correction_moins';

    public function isIncrement(): bool
    {
        return in_array($this, [
            self::RECEPTION_FOURNISSEUR,
            self::TROC_RECU,
            self::RETOUR_REVENDEUR,
            self::RETOUR_CLIENT,
            self::RETOUR_REPARATION,
            self::ANNULATION_VENTE,
            self::CORRECTION_PLUS,
        ]);
    }

    public function isDecrement(): bool
    {
        return ! $this->isIncrement();
    }

    public function label(): string
    {
        return match ($this) {
            self::RECEPTION_FOURNISSEUR => 'Réception fournisseur',
            self::TROC_RECU => 'Téléphone reçu en troc',
            self::RETOUR_REVENDEUR => 'Retour revendeur',
            self::RETOUR_CLIENT => 'Retour client',
            self::RETOUR_REPARATION => 'Retour de réparation',
            self::VENTE_DIRECTE => 'Vente directe',
            self::VENTE_TROC => 'Vente avec troc',
            self::DEPOT_REVENDEUR => 'Dépôt revendeur',
            self::ECHANGE_RETOUR => 'Échange suite retour',
            self::ENVOI_REPARATION => 'Envoi en réparation',
            self::RETOUR_FOURNISSEUR => 'Retour fournisseur',
            self::ANNULATION_VENTE => 'Annulation de vente',
            self::CASSE => 'Casse',
            self::VOL => 'Vol',
            self::PERTE => 'Perte',
            self::CORRECTION_PLUS => 'Correction (+)',
            self::CORRECTION_MOINS => 'Correction (-)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RECEPTION_FOURNISSEUR,
            self::RETOUR_REVENDEUR,
            self::RETOUR_CLIENT,
            self::RETOUR_REPARATION,
            self::TROC_RECU,
            self::ANNULATION_VENTE => 'green',

            self::VENTE_DIRECTE,
            self::VENTE_TROC => 'blue',

            self::DEPOT_REVENDEUR,
            self::ECHANGE_RETOUR => 'orange',

            self::ENVOI_REPARATION => 'yellow',

            self::CASSE,
            self::VOL,
            self::PERTE,
            self::RETOUR_FOURNISSEUR => 'red',

            self::CORRECTION_PLUS,
            self::CORRECTION_MOINS => 'purple',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RECEPTION_FOURNISSEUR => 'truck',
            self::TROC_RECU => 'repeat',
            self::RETOUR_REVENDEUR => 'package-plus',
            self::RETOUR_CLIENT => 'arrow-left-circle',
            self::RETOUR_REPARATION => 'wrench',
            self::VENTE_DIRECTE => 'shopping-cart',
            self::VENTE_TROC => 'shuffle',
            self::DEPOT_REVENDEUR => 'package-minus',
            self::ECHANGE_RETOUR => 'refresh-cw',
            self::ENVOI_REPARATION => 'tool',
            self::RETOUR_FOURNISSEUR => 'arrow-right-circle',
            self::ANNULATION_VENTE => 'rotate-ccw',
            self::CASSE => 'trash-2',
            self::VOL => 'alert-triangle',
            self::PERTE => 'x-circle',
            self::CORRECTION_PLUS,
            self::CORRECTION_MOINS => 'edit',
        };
    }

    public function requiresJustification(): bool
    {
        return in_array($this, [
            self::CASSE,
            self::VOL,
            self::PERTE,
            self::CORRECTION_PLUS,
            self::CORRECTION_MOINS,
        ]);
    }

    /**
     * Types nécessitant un IMEI
     */
    public function requiresImei(): bool
    {
        return in_array($this, [
            self::RECEPTION_FOURNISSEUR,
            self::TROC_RECU,
            self::VENTE_DIRECTE,
            self::VENTE_TROC,
            self::DEPOT_REVENDEUR,
            self::RETOUR_CLIENT,
            self::ECHANGE_RETOUR,
            self::ENVOI_REPARATION,
            self::RETOUR_REPARATION,
        ]);
    }

    /**
     * Types nécessitant info revendeur
     */
    public function requiresResellerInfo(): bool
    {
        return in_array($this, [
            self::DEPOT_REVENDEUR,
            self::RETOUR_REVENDEUR,
        ]);
    }

    /**
     * Types nécessitant produit de troc associé
     */
    public function requiresTradeProduct(): bool
    {
        return in_array($this, [
            self::VENTE_TROC,
            self::TROC_RECU,
        ]);
    }

    public static function grouped(): array
    {
        return [
            'Entrées stock' => [
                self::RECEPTION_FOURNISSEUR,
                self::TROC_RECU,
                self::RETOUR_REVENDEUR,
                self::RETOUR_CLIENT,
                self::RETOUR_REPARATION,
            ],
            'Ventes' => [
                self::VENTE_DIRECTE,
                self::VENTE_TROC,
                self::DEPOT_REVENDEUR,
            ],
            'Échanges et réparations' => [
                self::ECHANGE_RETOUR,
                self::ENVOI_REPARATION,
            ],
            'Sorties définitives' => [
                self::RETOUR_FOURNISSEUR,
                self::CASSE,
                self::VOL,
                self::PERTE,
            ],
            'Corrections' => [
                self::CORRECTION_PLUS,
                self::CORRECTION_MOINS,
            ],
        ];
    }

    /**
     * Types de mouvements autorisés pour un vendeur
     */
    public static function forVendeur(): array
    {
        return [
            self::VENTE_DIRECTE,
            self::VENTE_TROC,
            self::RETOUR_CLIENT,
            self::ECHANGE_RETOUR,
        ];
    }
}
