<?php
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

echo "Nettoyage rapide des accessoires...\n";

$productIds = Product::whereHas('productModel', function ($q) {
    $q->where('category', 'accessoire');
})->pluck('id')->toArray();

if (empty($productIds)) {
    echo "Aucun produit accessoire trouver ou deja nettoyer.\n";
    exit;
}

// Check for sales
$salesCount = DB::table('sales')->whereIn('product_id', $productIds)->count();
if ($salesCount > 0) {
    echo "Attention {$salesCount} ventes trouvees. Annulation.\n";
    exit;
}

$movementsDeleted = DB::table('stock_movements')->whereIn('product_id', $productIds)->delete();
$productsDeleted = DB::table('products')->whereIn('id', $productIds)->delete();

echo "Nettoyage termine. {$productsDeleted} produits supprimes, {$movementsDeleted} mouvements supprimes.\n";
