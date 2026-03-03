<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer toutes les permissions
        $permissions = [
            // Gestion utilisateurs
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Gestion produits
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Gestion stock
            'stock.view',
            'stock.entry',
            'stock.exit',
            'stock.adjustment',
            'stock.return',

            // Rapports
            'reports.view',
            'reports.export',

            // Catégories
            'categories.manage',

            // Revendeurs
            'resellers.manage',

            // Ventes
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',

            // Retours clients
            'returns.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer le rôle Admin avec toutes les permissions
        $adminRole = Role::create(['name' => UserRole::ADMIN->value]);
        $adminRole->givePermissionTo(Permission::all());

        // Créer le rôle Vendeur avec permissions limitées
        $vendeurRole = Role::create(['name' => UserRole::VENDEUR->value]);
        $vendeurRole->givePermissionTo([
            'products.view',
            'stock.view',
            'stock.exit',
            'stock.return',
            'sales.view',
            'sales.create',
            'returns.manage',
            'resellers.manage',
        ]);

        $this->command->info('✅ Rôles et permissions créés avec succès!');
        $this->command->info('   - Admin: '.$adminRole->permissions->count().' permissions');
        $this->command->info('   - Vendeur: '.$vendeurRole->permissions->count().' permissions');
    }
}
