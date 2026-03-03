<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Reseller;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Création des données de test...');

        DB::transaction(function () {
            // Créer des revendeurs d'abord
            $this->createResellers();
            
            // Créer les modèles de produits
            $this->createProductModels();
            
            $this->command->info('');
            $this->command->info('🎉 Données de test créées avec succès!');
        });
    }

    /**
     * Créer les revendeurs
     */
    private function createResellers(): void
    {
        $resellers = [
            [
                'name' => 'Jean Revendeur',
                'phone' => '+229 97 12 34 56',
                'address' => 'Cotonou, Akpakpa',
            ],
            [
                'name' => 'Marie Commerce',
                'phone' => '+229 96 78 90 12',
                'address' => 'Cotonou, Cadjèhoun',
            ],
            [
                'name' => 'Paul Distribution',
                'phone' => '+229 95 45 67 89',
                'address' => 'Porto-Novo',
            ],
        ];

        foreach ($resellers as $resellerData) {
            Reseller::create($resellerData);
        }

        $this->command->info('✅ ' . Reseller::count() . ' revendeurs créés');
    }

    /**
     * Créer les modèles de produits et leurs stocks
     */
    private function createProductModels(): void
    {
        $productModelsData = $this->getProductModelsData();

        foreach ($productModelsData as $modelData) {
            $model = ProductModel::create([
                'name'                 => $modelData['name'],
                'brand'                => $modelData['brand'],
                'category'             => $modelData['category'],
                'condition_type'       => $modelData['condition_type'] ?? 'neuve',
                'prix_revient_default' => $modelData['prix_revient_default'],
                'prix_vente_default'   => $modelData['prix_vente_default'],
                'prix_vente_revendeur' => $modelData['prix_vente_revendeur'],
                'stock_minimum'        => $modelData['stock_minimum'],
            ]);

            // Créer des produits selon la catégorie
            $this->createProductsForModel($model, $modelData['category']);
        }

        $this->command->info('✅ ' . ProductModel::count() . ' modèles de produits créés');
        $this->command->info('✅ ' . Product::count() . ' produits créés');
        $this->command->info('✅ ' . StockMovement::count() . ' mouvements de stock enregistrés');
    }

    /**
     * Créer des produits pour un modèle donné
     */
    private function createProductsForModel(ProductModel $model, string $category): void
    {
        // Déterminer la quantité selon la catégorie
        $quantity = match ($category) {
            'telephone' => rand(2, 4),
            'tablette' => rand(1, 3),
            'accessoire' => rand(5, 15),
        };

        for ($i = 1; $i <= $quantity; $i++) {
            $this->createSingleProduct($model, $category);
        }
    }

    /**
     * Créer un produit unique avec son mouvement de stock
     */
    private function createSingleProduct(ProductModel $model, string $category): void
    {
        // Préparer les données du produit
        $productData = [
            'product_model_id' => $model->id,
            'state' => ProductState::DISPONIBLE,
            'location' => ProductLocation::BOUTIQUE,
            'date_achat' => now()->subDays(rand(1, 60)),
            'fournisseur' => $this->getRandomSupplier(),
            'condition' => collect(['Neuf', 'Excellent', 'Bon'])->random(),
            'created_by' => 1, // Admin
        ];

        // Ajouter IMEI ou numéro de série selon la catégorie
        if (in_array($category, ['telephone', 'tablette'])) {
            $productData['imei'] = $this->generateFakeImei();
        } else {
            $productData['serial_number'] = $this->generateSerialNumber($model);
        }

        // Créer le produit
        $product = Product::create($productData);

        // Enregistrer le mouvement de stock (réception fournisseur)
        StockMovement::create([
            'product_id' => $product->id,
            'type' => StockMovementType::RECEPTION_FOURNISSEUR,
            'quantity' => 1,
            'state_before' => null,
            'state_after' => ProductState::DISPONIBLE,
            'location_before' => null,
            'location_after' => ProductLocation::BOUTIQUE,
            'notes' => 'Stock initial - Réception fournisseur',
            'user_id' => 1,
            'created_at' => $product->date_achat,
            'updated_at' => $product->date_achat,
        ]);

        // Ajouter quelques mouvements aléatoires pour certains produits
        if (rand(1, 100) > 70) {
            $this->addRandomMovements($product, $category);
        }
    }

    /**
     * Ajouter des mouvements aléatoires à un produit
     */
    private function addRandomMovements(Product $product, string $category): void
    {
        $movementTypes = [
            StockMovementType::ENVOI_REPARATION,
            StockMovementType::RETOUR_REPARATION,
            StockMovementType::DEPOT_REVENDEUR,
            StockMovementType::RETOUR_REVENDEUR,
        ];

        $selectedType = $movementTypes[array_rand($movementTypes)];
        
        $stateBefore = $product->state;
        $locationBefore = $product->location;

        // Déterminer l'état et la localisation après le mouvement
        [$stateAfter, $locationAfter] = $this->getStateAndLocationForMovement($selectedType);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $selectedType,
            'quantity' => 1,
            'state_before' => $stateBefore,
            'state_after' => $stateAfter,
            'location_before' => $locationBefore,
            'location_after' => $locationAfter,
            'reseller_id' => in_array($selectedType, [
                StockMovementType::DEPOT_REVENDEUR,
                StockMovementType::RETOUR_REVENDEUR
            ]) ? Reseller::inRandomOrder()->first()?->id : null,
            'notes' => 'Mouvement de test',
            'user_id' => 1,
            'created_at' => now()->subDays(rand(1, 30)),
        ]);

        // Mettre à jour le produit
        $product->update([
            'state' => $stateAfter,
            'location' => $locationAfter,
        ]);
    }

    /**
     * Obtenir l'état et la localisation selon le type de mouvement
     */
    private function getStateAndLocationForMovement(StockMovementType $type): array
    {
        return match ($type) {
            StockMovementType::ENVOI_REPARATION => [
                ProductState::A_REPARER,
                ProductLocation::BOUTIQUE
            ],
            StockMovementType::RETOUR_REPARATION => [
                ProductState::DISPONIBLE,
                ProductLocation::BOUTIQUE
            ],
            StockMovementType::DEPOT_REVENDEUR => [
                ProductState::DISPONIBLE,
                ProductLocation::CHEZ_REVENDEUR
            ],
            StockMovementType::RETOUR_REVENDEUR => [
                ProductState::DISPONIBLE,
                ProductLocation::BOUTIQUE
            ],
            default => [
                ProductState::DISPONIBLE,
                ProductLocation::BOUTIQUE
            ],
        };
    }

    /**
     * Générer un faux IMEI (15 chiffres)
     */
    private function generateFakeImei(): string
    {
        return '35' . rand(1000000, 9999999) . rand(100000, 999999);
    }

    /**
     * Générer un numéro de série pour accessoire
     */
    private function generateSerialNumber(ProductModel $model): string
    {
        $prefix = strtoupper(substr($model->brand, 0, 3));
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Obtenir un fournisseur aléatoire
     */
    private function getRandomSupplier(): string
    {
        $suppliers = [
            'Fournisseur International',
            'Import Tech SARL',
            'Digital Supply Co.',
            'TechSource Bénin',
            'Global Electronics',
        ];

        return $suppliers[array_rand($suppliers)];
    }

    /**
     * Obtenir les données des modèles de produits
     */
    private function getProductModelsData(): array
    {
        return [
            // === TÉLÉPHONES — NEUF ===
            // iPhones - Série 7
            ['name' => 'iPhone 7 32GB',           'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 80000,  'prix_vente_default' => 110000, 'prix_vente_revendeur' => 100000, 'stock_minimum' => 2],
            ['name' => 'iPhone 7 128GB',           'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 90000,  'prix_vente_default' => 120000, 'prix_vente_revendeur' => 110000, 'stock_minimum' => 2],
            ['name' => 'iPhone 7 Plus 32GB',       'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 100000, 'prix_vente_default' => 135000, 'prix_vente_revendeur' => 122000, 'stock_minimum' => 2],

            // iPhones - Série 11
            ['name' => 'iPhone 11 64GB',           'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 250000, 'prix_vente_default' => 320000, 'prix_vente_revendeur' => 295000, 'stock_minimum' => 3],
            ['name' => 'iPhone 11 128GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 270000, 'prix_vente_default' => 340000, 'prix_vente_revendeur' => 312000, 'stock_minimum' => 3],
            ['name' => 'iPhone 11 Pro 64GB',       'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 320000, 'prix_vente_default' => 410000, 'prix_vente_revendeur' => 378000, 'stock_minimum' => 2],

            // iPhones - Série 13
            ['name' => 'iPhone 13 128GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 400000, 'prix_vente_default' => 520000, 'prix_vente_revendeur' => 478000, 'stock_minimum' => 3],
            ['name' => 'iPhone 13 256GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 430000, 'prix_vente_default' => 550000, 'prix_vente_revendeur' => 505000, 'stock_minimum' => 3],
            ['name' => 'iPhone 13 Pro 128GB',      'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 480000, 'prix_vente_default' => 620000, 'prix_vente_revendeur' => 570000, 'stock_minimum' => 2],

            // iPhones - Série 14
            ['name' => 'iPhone 14 128GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 480000, 'prix_vente_default' => 620000, 'prix_vente_revendeur' => 570000, 'stock_minimum' => 3],
            ['name' => 'iPhone 14 Plus 128GB',     'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 530000, 'prix_vente_default' => 680000, 'prix_vente_revendeur' => 625000, 'stock_minimum' => 2],
            ['name' => 'iPhone 14 Pro 128GB',      'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 600000, 'prix_vente_default' => 770000, 'prix_vente_revendeur' => 710000, 'stock_minimum' => 2],
            ['name' => 'iPhone 14 Pro Max 256GB',  'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 700000, 'prix_vente_default' => 880000, 'prix_vente_revendeur' => 810000, 'stock_minimum' => 2],

            // iPhones - Série 15
            ['name' => 'iPhone 15 128GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 550000, 'prix_vente_default' => 710000, 'prix_vente_revendeur' => 653000, 'stock_minimum' => 3],
            ['name' => 'iPhone 15 Plus 256GB',     'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 670000, 'prix_vente_default' => 840000, 'prix_vente_revendeur' => 773000, 'stock_minimum' => 2],
            ['name' => 'iPhone 15 Pro 256GB',      'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 750000, 'prix_vente_default' => 940000, 'prix_vente_revendeur' => 865000, 'stock_minimum' => 2],
            ['name' => 'iPhone 15 Pro Max 512GB',  'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 870000, 'prix_vente_default' => 1070000, 'prix_vente_revendeur' => 984000, 'stock_minimum' => 2],

            // iPhones - Série 16
            ['name' => 'iPhone 16 128GB',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 650000, 'prix_vente_default' => 830000, 'prix_vente_revendeur' => 764000, 'stock_minimum' => 3],
            ['name' => 'iPhone 16 Pro 256GB',      'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 850000, 'prix_vente_default' => 1060000, 'prix_vente_revendeur' => 975000, 'stock_minimum' => 2],
            ['name' => 'iPhone 16 Pro Max 512GB',  'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 970000, 'prix_vente_default' => 1190000, 'prix_vente_revendeur' => 1095000, 'stock_minimum' => 2],

            // SAMSUNG - Neuf
            ['name' => 'Samsung Galaxy S21 128GB',        'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 300000, 'prix_vente_default' => 400000, 'prix_vente_revendeur' => 368000, 'stock_minimum' => 2],
            ['name' => 'Samsung Galaxy S22 256GB',        'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 410000, 'prix_vente_default' => 530000, 'prix_vente_revendeur' => 488000, 'stock_minimum' => 2],
            ['name' => 'Samsung Galaxy S23 256GB',        'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 480000, 'prix_vente_default' => 620000, 'prix_vente_revendeur' => 570000, 'stock_minimum' => 2],
            ['name' => 'Samsung Galaxy S24 256GB',        'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 560000, 'prix_vente_default' => 720000, 'prix_vente_revendeur' => 662000, 'stock_minimum' => 3],
            ['name' => 'Samsung Galaxy S24 Ultra 512GB',  'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 750000, 'prix_vente_default' => 950000, 'prix_vente_revendeur' => 874000, 'stock_minimum' => 2],
            ['name' => 'Samsung Galaxy Z Flip 5 256GB',   'brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'neuve',   'prix_revient_default' => 550000, 'prix_vente_default' => 720000, 'prix_vente_revendeur' => 662000, 'stock_minimum' => 2],

            // === TÉLÉPHONES — OCCASION ===
            ['name' => 'iPhone 7 64GB (Occasion)',           'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 45000,  'prix_vente_default' => 65000,  'prix_vente_revendeur' => 58000,  'stock_minimum' => 1],
            ['name' => 'iPhone 11 64GB (Occasion)',          'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 180000, 'prix_vente_default' => 235000, 'prix_vente_revendeur' => 215000, 'stock_minimum' => 2],
            ['name' => 'iPhone 12 128GB (Occasion)',         'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 230000, 'prix_vente_default' => 300000, 'prix_vente_revendeur' => 275000, 'stock_minimum' => 2],
            ['name' => 'iPhone 13 128GB (Occasion)',         'brand' => 'Apple',   'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 300000, 'prix_vente_default' => 390000, 'prix_vente_revendeur' => 358000, 'stock_minimum' => 2],
            ['name' => 'Samsung Galaxy S20 128GB (Occasion)','brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 180000, 'prix_vente_default' => 240000, 'prix_vente_revendeur' => 220000, 'stock_minimum' => 1],
            ['name' => 'Samsung Galaxy S21 128GB (Occasion)','brand' => 'Samsung', 'category' => 'telephone', 'condition_type' => 'occasion', 'prix_revient_default' => 220000, 'prix_vente_default' => 290000, 'prix_vente_revendeur' => 267000, 'stock_minimum' => 1],

            // === TABLETTES ===
            ['name' => 'iPad 9ème génération 64GB WiFi',      'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 220000, 'prix_vente_default' => 290000, 'prix_vente_revendeur' => 267000, 'stock_minimum' => 2],
            ['name' => 'iPad 10ème génération 64GB WiFi',     'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 280000, 'prix_vente_default' => 370000, 'prix_vente_revendeur' => 340000, 'stock_minimum' => 2],
            ['name' => 'iPad 10ème génération 256GB WiFi',    'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 320000, 'prix_vente_default' => 410000, 'prix_vente_revendeur' => 377000, 'stock_minimum' => 2],
            ['name' => 'iPad Air 5ème génération 64GB WiFi',  'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 380000, 'prix_vente_default' => 500000, 'prix_vente_revendeur' => 460000, 'stock_minimum' => 2],
            ['name' => 'iPad Air 5ème génération 256GB WiFi', 'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 450000, 'prix_vente_default' => 580000, 'prix_vente_revendeur' => 534000, 'stock_minimum' => 2],
            ['name' => 'iPad Pro 11" M2 128GB WiFi',          'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 550000, 'prix_vente_default' => 720000, 'prix_vente_revendeur' => 662000, 'stock_minimum' => 2],
            ['name' => 'iPad Pro 12.9" M2 256GB WiFi',        'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 750000, 'prix_vente_default' => 970000, 'prix_vente_revendeur' => 893000, 'stock_minimum' => 1],
            ['name' => 'iPad Mini 6ème génération 64GB WiFi', 'brand' => 'Apple', 'category' => 'tablette', 'condition_type' => 'neuve',   'prix_revient_default' => 320000, 'prix_vente_default' => 420000, 'prix_vente_revendeur' => 386000, 'stock_minimum' => 2],

            // === ACCESSOIRES ===
            // Écouteurs
            ['name' => 'AirPods 2ème génération',        'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 50000,  'prix_vente_default' => 70000,  'prix_vente_revendeur' => 63000,  'stock_minimum' => 5],
            ['name' => 'AirPods 3ème génération',        'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 70000,  'prix_vente_default' => 95000,  'prix_vente_revendeur' => 87000,  'stock_minimum' => 5],
            ['name' => 'AirPods Pro 2',                  'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 80000,  'prix_vente_default' => 110000, 'prix_vente_revendeur' => 100000, 'stock_minimum' => 5],
            ['name' => 'Samsung Galaxy Buds 2',          'brand' => 'Samsung', 'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 35000,  'prix_vente_default' => 50000,  'prix_vente_revendeur' => 45000,  'stock_minimum' => 5],
            ['name' => 'Samsung Galaxy Buds 2 Pro',      'brand' => 'Samsung', 'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 55000,  'prix_vente_default' => 75000,  'prix_vente_revendeur' => 68000,  'stock_minimum' => 5],

            // Chargeurs
            ['name' => 'Chargeur iPhone 20W USB-C',      'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 5000,   'prix_vente_default' => 8000,   'prix_vente_revendeur' => 7000,   'stock_minimum' => 10],
            ['name' => 'Chargeur iPhone 30W USB-C',      'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 12000,  'prix_vente_default' => 18000,  'prix_vente_revendeur' => 16000,  'stock_minimum' => 8],
            ['name' => 'Chargeur Samsung 25W USB-C',     'brand' => 'Samsung', 'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 4000,   'prix_vente_default' => 7000,   'prix_vente_revendeur' => 6000,   'stock_minimum' => 10],
            ['name' => 'Chargeur sans fil MagSafe',      'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 15000,  'prix_vente_default' => 22000,  'prix_vente_revendeur' => 20000,  'stock_minimum' => 8],

            // Câbles
            ['name' => 'Câble USB-C vers Lightning 1m',  'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 4000,   'prix_vente_default' => 7000,   'prix_vente_revendeur' => 6000,   'stock_minimum' => 15],
            ['name' => 'Câble USB-C vers Lightning 2m',  'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 6000,   'prix_vente_default' => 9000,   'prix_vente_revendeur' => 8000,   'stock_minimum' => 10],
            ['name' => 'Câble USB-C vers USB-C 1m',      'brand' => 'Apple',   'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 4000,   'prix_vente_default' => 7000,   'prix_vente_revendeur' => 6000,   'stock_minimum' => 15],

            // Coques et Protection
            ['name' => 'Coque Silicone iPhone 13/14',            'brand' => 'Apple',    'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 8000,  'prix_vente_default' => 12000, 'prix_vente_revendeur' => 10500, 'stock_minimum' => 10],
            ['name' => 'Coque Silicone iPhone 15/16',            'brand' => 'Apple',    'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 9000,  'prix_vente_default' => 14000, 'prix_vente_revendeur' => 12500, 'stock_minimum' => 10],
            ['name' => 'Coque Transparente iPhone Universelle',  'brand' => 'Générique','category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 2000,  'prix_vente_default' => 4000,  'prix_vente_revendeur' => 3500,  'stock_minimum' => 20],
            ['name' => 'Protection écran verre trempé iPhone',   'brand' => 'Générique','category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 2000,  'prix_vente_default' => 4000,  'prix_vente_revendeur' => 3500,  'stock_minimum' => 25],
            ['name' => 'Protection écran verre trempé Samsung',  'brand' => 'Générique','category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 2000,  'prix_vente_default' => 4000,  'prix_vente_revendeur' => 3500,  'stock_minimum' => 20],

            // Autres
            ['name' => 'Support voiture magnétique',  'brand' => 'Générique', 'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 3000,  'prix_vente_default' => 6000,  'prix_vente_revendeur' => 5500,  'stock_minimum' => 10],
            ['name' => 'Batterie externe 10000mAh',   'brand' => 'Anker',     'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 12000, 'prix_vente_default' => 18000, 'prix_vente_revendeur' => 16500, 'stock_minimum' => 8],
            ['name' => 'Batterie externe 20000mAh',   'brand' => 'Anker',     'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 18000, 'prix_vente_default' => 26000, 'prix_vente_revendeur' => 23900, 'stock_minimum' => 5],
            ['name' => 'Apple Pencil 2ème génération','brand' => 'Apple',     'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 50000, 'prix_vente_default' => 70000, 'prix_vente_revendeur' => 64000, 'stock_minimum' => 5],
            ['name' => 'Smart Folio iPad',            'brand' => 'Apple',     'category' => 'accessoire', 'condition_type' => 'neuve', 'prix_revient_default' => 25000, 'prix_vente_default' => 38000, 'prix_vente_revendeur' => 35000, 'stock_minimum' => 5],
        ];
    }
}