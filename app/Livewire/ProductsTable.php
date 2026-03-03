<?php

namespace App\Livewire;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Models\Product;
use App\Models\ProductModel;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsTable extends Component
{
    use WithPagination;

    public $step = 1;

    public $selectedCategory = '';

    public $search = '';

    public $state = '';

    public $location = '';

    public $product_model_id = '';

    public $condition = '';

    protected $queryString = [
        'step' => ['except' => 1],
        'selectedCategory' => ['except' => ''],
        'search' => ['except' => ''],
        'state' => ['except' => ''],
        'location' => ['except' => ''],
        'product_model_id' => ['except' => ''],
        'condition' => ['except' => ''],
    ];

    public function selectCategory($category)
    {
        $this->selectedCategory = $category;
        $this->step = 2;
        $this->resetPage();
    }

    public function backToCategories()
    {
        $this->step = 1;
        $this->selectedCategory = '';
        $this->resetFilters();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingState()
    {
        $this->resetPage();
    }

    public function updatingLocation()
    {
        $this->resetPage();
    }

    public function updatingProductModelId()
    {
        $this->resetPage();
    }

    public function updatingCondition()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'state', 'location', 'product_model_id', 'condition']);
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        // OPTIMISATION MAXIMALE: 2 requêtes au total au lieu de N+1
        // 1. Récupérer les catégories avec le nombre de modèles
        $categoriesWithModels = ProductModel::selectRaw('
                category,
                COUNT(*) as models_count
            ')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->whereNull('deleted_at')
            ->groupBy('category')
            ->havingRaw('COUNT(*) > 0')
            ->pluck('models_count', 'category');

        if ($categoriesWithModels->isEmpty()) {
            return collect([]);
        }

        // 2. Récupérer TOUS les comptes de produits et les stats financières en UNE SEULE requête
        $categoryStats = Product::join('product_models', 'products.product_model_id', '=', 'product_models.id')
            ->whereIn('product_models.category', $categoriesWithModels->keys())
            ->whereNull('products.deleted_at')
            ->whereNull('product_models.deleted_at')
            ->selectRaw('
                product_models.category, 
                COUNT(*) as products_count,
                SUM(CASE WHEN products.state != ? THEN product_models.prix_revient_default ELSE 0 END) as investissement,
                SUM(CASE WHEN products.state != ? THEN product_models.prix_vente_default ELSE 0 END) as revenu_espere
            ', [ProductState::VENDU->value, ProductState::VENDU->value])
            ->groupBy('product_models.category')
            ->get()
            ->keyBy('category');

        // 3. Mapper les résultats
        return $categoriesWithModels->map(function ($modelsCount, $category) use ($categoryStats) {
            $stats = $categoryStats[$category] ?? null;
            $investissement = $stats ? (float) $stats->investissement : 0;
            $revenu = $stats ? (float) $stats->revenu_espere : 0;

            return [
                'value' => $category,
                'label' => $this->getCategoryLabel($category),
                'icon' => $this->getCategoryIcon($category),
                'models_count' => $modelsCount,
                'products_count' => $stats ? $stats->products_count : 0,
                'investissement' => $investissement,
                'revenu' => $revenu,
                'benefice' => $revenu - $investissement,
            ];
        })->values();
    }

    #[Computed]
    public function products()
    {
        if ($this->step !== 2 || ! $this->selectedCategory) {
            return collect();
        }

        $query = Product::with(['productModel'])
            ->whereHas('productModel', function ($q) {
                $q->where('category', $this->selectedCategory);
            });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('imei', 'LIKE', '%'.$search.'%')
                    ->orWhere('serial_number', 'LIKE', '%'.$search.'%')
                    ->orWhereHas('productModel', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%'.$search.'%')
                            ->orWhere('brand', 'LIKE', '%'.$search.'%');
                    });
            });
        }

        if ($this->state !== '' && $this->state !== null) {
            $query->where('state', $this->state);
        }

        if ($this->location !== '' && $this->location !== null) {
            $query->where('location', $this->location);
        }

        if ($this->product_model_id !== '' && $this->product_model_id !== null) {
            $query->where('product_model_id', $this->product_model_id);
        }

        if ($this->condition !== '' && $this->condition !== null) {
            $query->where('condition', $this->condition);
        }

        return $query->latest()->paginate(15);
    }

    #[Computed]
    public function stats()
    {
        if ($this->step !== 2 || ! $this->selectedCategory) {
            return [
                'total' => 0,
                'available' => 0,
                'chez_revendeur' => 0,
                'a_reparer' => 0,
            ];
        }

        // OPTIMISATION: Une seule requête au lieu de 4
        $stats = Product::whereHas('productModel', function ($q) {
            $q->where('category', $this->selectedCategory);
        })
            ->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN state = ? AND location = ? THEN 1 END) as available,
            COUNT(CASE WHEN location = ? THEN 1 END) as chez_revendeur,
            COUNT(CASE WHEN state = ? THEN 1 END) as a_reparer
        ', [
                ProductState::DISPONIBLE->value,
                ProductLocation::BOUTIQUE->value,
                ProductLocation::CHEZ_REVENDEUR->value,
                ProductState::A_REPARER->value,
            ])
            ->first();

        return [
            'total' => (int) $stats->total,
            'available' => (int) $stats->available,
            'chez_revendeur' => (int) $stats->chez_revendeur,
            'a_reparer' => (int) $stats->a_reparer,
        ];
    }

    #[Computed]
    public function productModels()
    {
        if ($this->step !== 2 || ! $this->selectedCategory) {
            return collect();
        }

        return ProductModel::where('is_active', true)
            ->where('category', $this->selectedCategory)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function states()
    {
        return collect(ProductState::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    #[Computed]
    public function locations()
    {
        return collect(ProductLocation::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    #[Computed]
    public function conditions()
    {
        if ($this->step !== 2 || ! $this->selectedCategory) {
            return [];
        }

        // OPTIMISATION: Cache le résultat
        return cache()->remember(
            "conditions_{$this->selectedCategory}",
            now()->addHour(),
            fn () => Product::whereHas('productModel', function ($q) {
                $q->where('category', $this->selectedCategory);
            })
                ->whereNotNull('condition')
                ->where('condition', '!=', '')
                ->distinct()
                ->pluck('condition')
                ->toArray()
        );
    }

    private function getCategoryLabel($category)
    {
        return match ($category) {
            'telephone' => 'Téléphones',
            'tablette' => 'Tablettes',
            'pc' => 'Ordinateurs',
            'accessoire' => 'Accessoires',
            default => ucfirst($category),
        };
    }

    private function getCategoryIcon($category)
    {
        return match ($category) {
            'telephone' => 'smartphone',
            'tablette' => 'tablet',
            'pc' => 'monitor',
            'accessoire' => 'box',
            default => 'box',
        };
    }

    public function render()
    {
        $benchmarks = [];

        $start = microtime(true);
        $categories = $this->step === 1 ? $this->categories : collect();
        $benchmarks['categories'] = round((microtime(true) - $start) * 1000, 2);

        $start = microtime(true);
        $products = $this->step === 2 ? $this->products : collect();
        $benchmarks['products'] = round((microtime(true) - $start) * 1000, 2);

        $start = microtime(true);
        $stats = $this->step === 2 ? $this->stats : ['total' => 0, 'available' => 0, 'chez_revendeur' => 0, 'a_reparer' => 0];
        $benchmarks['stats'] = round((microtime(true) - $start) * 1000, 2);

        return view('livewire.products-table', [
            'categories' => $categories,
            'products' => $products,
            'stats' => $stats,
            'productModels' => $this->productModels,
            'states' => $this->states,
            'locations' => $this->locations,
            'conditions' => $this->conditions,
        ]);
    }
}
