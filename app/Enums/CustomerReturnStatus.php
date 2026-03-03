<?php

namespace App\Enums;

/**
 * Statut du workflow de retour client
 */
enum CustomerReturnStatus: string
{
    case EN_ATTENTE        = 'en_attente';
    case EN_REPARATION     = 'en_reparation';
    case RETOUR_FOURNISSEUR = 'retour_fournisseur';
    case REMPLACE          = 'remplace';
    case RESOLU            = 'resolu';
    case CLOS              = 'clos';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE         => 'En attente',
            self::EN_REPARATION      => 'En réparation',
            self::RETOUR_FOURNISSEUR => 'Retour fournisseur',
            self::REMPLACE           => 'Remplacé',
            self::RESOLU             => 'Résolu',
            self::CLOS               => 'Clos',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EN_ATTENTE         => 'amber',
            self::EN_REPARATION      => 'blue',
            self::RETOUR_FOURNISSEUR => 'orange',
            self::REMPLACE           => 'purple',
            self::RESOLU             => 'green',
            self::CLOS               => 'gray',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::EN_ATTENTE         => 'bg-amber-100 text-amber-800',
            self::EN_REPARATION      => 'bg-blue-100 text-blue-800',
            self::RETOUR_FOURNISSEUR => 'bg-orange-100 text-orange-800',
            self::REMPLACE           => 'bg-purple-100 text-purple-800',
            self::RESOLU             => 'bg-emerald-100 text-emerald-800',
            self::CLOS               => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Actions possibles depuis ce statut
     */
    public function availableActions(): array
    {
        return match ($this) {
            self::EN_ATTENTE         => ['mettre_en_reparation', 'envoyer_fournisseur', 'resoudre', 'clore'],
            self::EN_REPARATION      => ['marquer_repare', 'envoyer_fournisseur'],
            self::RETOUR_FOURNISSEUR => ['confirmer_remplacement', 'marquer_resolu'],
            self::REMPLACE           => ['clore'],
            self::RESOLU             => ['clore'],
            self::CLOS               => [],
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::CLOS, self::RESOLU]);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
