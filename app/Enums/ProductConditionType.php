<?php

namespace App\Enums;

/**
 * Condition commerciale d'un modèle de produit
 */
enum ProductConditionType: string
{
    case NEUF     = 'neuf';
    case VENU     = 'venu';
    case OCCASION = 'occasion';

    public function label(): string
    {
        return match ($this) {
            self::NEUF     => 'Neuf',
            self::VENU     => 'Venu',
            self::OCCASION => 'Occasion',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEUF     => 'blue',
            self::VENU     => 'purple',
            self::OCCASION => 'amber',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NEUF     => 'bg-blue-100 text-blue-800 border border-blue-200',
            self::VENU     => 'bg-purple-100 text-purple-800 border border-purple-200',
            self::OCCASION => 'bg-amber-100 text-amber-800 border border-amber-200',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
