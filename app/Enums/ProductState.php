<?php

namespace App\Enums;

/**
 * État fonctionnel d'un produit
 */
enum ProductState: string
{
    case DISPONIBLE = 'disponible';
    case VENDU = 'vendu';
    case A_REPARER = 'a_reparer';
    case REPARE = 'repare';
    case RETOUR = 'retour';
    case PERDU = 'perdu';
    case RETOUR_FOURNISSEUR = 'retour_fournisseur';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIBLE         => 'Disponible',
            self::VENDU              => 'Vendu',
            self::A_REPARER          => 'À réparer',
            self::REPARE             => 'Réparé',
            self::RETOUR             => 'Retour',
            self::PERDU              => 'Perdu',
            self::RETOUR_FOURNISSEUR => 'Retour fournisseur',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DISPONIBLE, self::REPARE => 'success',
            self::VENDU => 'info',
            self::A_REPARER => 'warning',
            self::RETOUR => 'warning',
            self::PERDU => 'danger',
        };
    }

    /**
     * Badge Tailwind classes
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::DISPONIBLE, self::REPARE => 'bg-emerald-100 text-emerald-800',
            self::VENDU => 'bg-blue-100 text-blue-800',
            self::A_REPARER, self::RETOUR => 'bg-amber-100 text-amber-800',
            self::PERDU => 'bg-rose-100 text-rose-800',
        };
    }

    /**
     * Indique si le produit est disponible à la vente
     */
    public function isAvailable(): bool
    {
        return in_array($this, [
            self::DISPONIBLE,
            self::REPARE,
        ]);
    }

    /**
     * Indique si le produit peut être réparé
     */
    public function canBeRepaired(): bool
    {
        return in_array($this, [
            self::A_REPARER,
            self::RETOUR,
        ]);
    }

    /**
     * Options pour select
     */
    public static function options(): array
    {
        return array_map(
            fn (self $state) => [
                'value' => $state->value,
                'label' => $state->label(),
            ],
            self::cases()
        );
    }
}
