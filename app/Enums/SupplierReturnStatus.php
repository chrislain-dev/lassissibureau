<?php

namespace App\Enums;

/**
 * Statut d'un retour fournisseur
 */
enum SupplierReturnStatus: string
{
    case EN_ATTENTE = 'en_attente';
    case RECU       = 'recu';
    case REMPLACE   = 'remplace';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::RECU       => 'Reçu par le fournisseur',
            self::REMPLACE   => 'Remplacé',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'bg-amber-100 text-amber-800',
            self::RECU       => 'bg-blue-100 text-blue-800',
            self::REMPLACE   => 'bg-emerald-100 text-emerald-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
