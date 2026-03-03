<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\SaleType;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;
    private User $admin;
    private ProductModel $productModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = app(SaleService::class);

        // Créer un utilisateur admin avec les rôles
        $this->admin = User::factory()->create();

        // Créer un modèle de produit
        $this->productModel = ProductModel::factory()->create([
            'name' => 'iPhone 14',
            'brand' => 'Apple',
            'category' => 'telephone',
            'prix_revient_default' => 500000,
            'prix_vente_default' => 650000,
        ]);

        // Authentifier l'utilisateur
        $this->actingAs($this->admin);
    }

    /** @test */
    public function it_can_create_a_direct_sale(): void
    {
        // Créer un produit disponible
        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::DISPONIBLE->value,
            'location' => ProductLocation::BOUTIQUE->value,
            'prix_achat' => 500000,
            'prix_vente' => 650000,
            'created_by' => $this->admin->id,
        ]);

        $saleData = [
            'product_id' => $product->id,
            'sale_type' => SaleType::ACHAT_DIRECT->value,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'client_name' => 'Jean Dupont',
            'client_phone' => '0600000000',
            'date_vente_effective' => now(),
            'is_confirmed' => true,
            'sold_by' => $this->admin->id,
        ];

        $sale = $this->saleService->createSale($saleData);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals($product->id, $sale->product_id);
        $this->assertTrue($sale->is_confirmed);
        $this->assertEquals(SaleType::ACHAT_DIRECT, $sale->sale_type);
        $this->assertEquals(650000, $sale->prix_vente);

        // Vérifier que le produit a changé d'état
        $product->refresh();
        $this->assertEquals(ProductState::VENDU, $product->state);
        $this->assertEquals(ProductLocation::CHEZ_CLIENT, $product->location);
    }

    /** @test */
    public function it_can_create_a_trade_in_sale(): void
    {
        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::DISPONIBLE->value,
            'location' => ProductLocation::BOUTIQUE->value,
            'prix_achat' => 500000,
            'prix_vente' => 650000,
            'created_by' => $this->admin->id,
        ]);

        $saleData = [
            'product_id' => $product->id,
            'sale_type' => SaleType::TROC->value,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'client_name' => 'Marie Martin',
            'date_vente_effective' => now(),
            'is_confirmed' => true,
            'sold_by' => $this->admin->id,
            'has_trade_in' => true,
            'trade_in' => [
                'modele_recu' => 'iPhone 12',
                'imei_recu' => '123456789012345',
                'valeur_reprise' => 200000,
                'complement_especes' => 450000,
                'etat_recu' => 'Bon état, quelques rayures',
            ],
        ];

        $sale = $this->saleService->createSale($saleData);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals(SaleType::TROC, $sale->sale_type);
        $this->assertNotNull($sale->tradeIn);
        $this->assertEquals(200000, $sale->tradeIn->valeur_reprise);
        $this->assertEquals('123456789012345', $sale->tradeIn->imei_recu);
    }

    /** @test */
    public function it_can_create_a_reseller_sale(): void
    {
        $reseller = Reseller::factory()->create([
            'name' => 'Revendeur Test',
            'phone' => '0611111111',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::DISPONIBLE->value,
            'location' => ProductLocation::BOUTIQUE->value,
            'prix_achat' => 500000,
            'prix_vente' => 650000,
            'created_by' => $this->admin->id,
        ]);

        $saleData = [
            'product_id' => $product->id,
            'sale_type' => SaleType::ACHAT_DIRECT->value,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'reseller_id' => $reseller->id,
            'date_depot_revendeur' => now(),
            'date_vente_effective' => now(),
            'is_confirmed' => false, // Pas encore confirmé
            'payment_status' => PaymentStatus::UNPAID->value,
            'amount_paid' => 0,
            'payment_due_date' => now()->addDays(30),
            'sold_by' => $this->admin->id,
        ];

        $sale = $this->saleService->createSale($saleData);

        $this->assertFalse($sale->is_confirmed);
        $this->assertEquals($reseller->id, $sale->reseller_id);
        $this->assertEquals(PaymentStatus::UNPAID, $sale->payment_status);

        // Vérifier que le produit est chez le revendeur
        $product->refresh();
        $this->assertEquals(ProductLocation::CHEZ_REVENDEUR, $product->location);
    }

    /** @test */
    public function it_can_confirm_a_reseller_sale(): void
    {
        $reseller = Reseller::factory()->create();

        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::DISPONIBLE->value,
            'location' => ProductLocation::CHEZ_REVENDEUR->value,
            'prix_achat' => 500000,
            'prix_vente' => 650000,
            'created_by' => $this->admin->id,
        ]);

        $sale = Sale::factory()->create([
            'product_id' => $product->id,
            'reseller_id' => $reseller->id,
            'is_confirmed' => false,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'payment_status' => PaymentStatus::UNPAID,
            'amount_paid' => 0,
            'amount_remaining' => 650000,
            'sold_by' => $this->admin->id,
        ]);

        $confirmedSale = $this->saleService->confirmResellerSale($sale, [
            'payment_amount' => 650000,
            'payment_method' => 'cash',
        ]);

        $this->assertTrue($confirmedSale->is_confirmed);
        $this->assertNotNull($confirmedSale->date_confirmation_vente);

        // Vérifier que le produit est maintenant chez le client
        $product->refresh();
        $this->assertEquals(ProductState::VENDU, $product->state);
        $this->assertEquals(ProductLocation::CHEZ_CLIENT, $product->location);
    }

    /** @test */
    public function it_can_record_payment(): void
    {
        $reseller = Reseller::factory()->create();

        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::VENDU->value,
            'location' => ProductLocation::CHEZ_CLIENT->value,
            'created_by' => $this->admin->id,
        ]);

        $sale = Sale::factory()->create([
            'product_id' => $product->id,
            'reseller_id' => $reseller->id,
            'is_confirmed' => true,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'payment_status' => PaymentStatus::UNPAID,
            'amount_paid' => 0,
            'amount_remaining' => 650000,
            'sold_by' => $this->admin->id,
        ]);

        // Premier paiement partiel
        $payment = $this->saleService->recordPayment($sale, 300000, [
            'payment_method' => 'cash',
            'notes' => 'Acompte',
        ]);

        $sale->refresh();
        $this->assertEquals(300000, $sale->amount_paid);
        $this->assertEquals(350000, $sale->amount_remaining);
        $this->assertEquals(PaymentStatus::PARTIAL, $sale->payment_status);

        // Paiement final
        $this->saleService->recordPayment($sale, 350000, [
            'payment_method' => 'mobile_money',
        ]);

        $sale->refresh();
        $this->assertEquals(650000, $sale->amount_paid);
        $this->assertEquals(0, $sale->amount_remaining);
        $this->assertEquals(PaymentStatus::PAID, $sale->payment_status);
    }

    /** @test */
    public function it_throws_exception_for_unavailable_product(): void
    {
        // Créer un produit vendu (non disponible)
        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::VENDU->value,
            'location' => ProductLocation::CHEZ_CLIENT->value,
            'created_by' => $this->admin->id,
        ]);

        $saleData = [
            'product_id' => $product->id,
            'sale_type' => SaleType::ACHAT_DIRECT->value,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'date_vente_effective' => now(),
            'is_confirmed' => true,
            'sold_by' => $this->admin->id,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Ce produit n'est pas disponible à la vente.");

        $this->saleService->createSale($saleData);
    }

    /** @test */
    public function it_can_return_product_from_reseller(): void
    {
        $reseller = Reseller::factory()->create();

        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::DISPONIBLE->value,
            'location' => ProductLocation::CHEZ_REVENDEUR->value,
            'created_by' => $this->admin->id,
        ]);

        $sale = Sale::factory()->create([
            'product_id' => $product->id,
            'reseller_id' => $reseller->id,
            'is_confirmed' => false,
            'prix_vente' => 650000,
            'sold_by' => $this->admin->id,
        ]);

        $returnedSale = $this->saleService->returnFromReseller($sale, 'Produit invendu après 30 jours');

        // Vérifier que le produit est de retour en boutique
        $product->refresh();
        $this->assertEquals(ProductState::DISPONIBLE, $product->state);
        $this->assertEquals(ProductLocation::BOUTIQUE, $product->location);

        // Vérifier que la vente est soft-deleted
        $this->assertSoftDeleted($sale);
    }

    /** @test */
    public function it_calculates_sale_stats_correctly(): void
    {
        // Créer plusieurs ventes confirmées
        $product1 = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::VENDU->value,
            'location' => ProductLocation::CHEZ_CLIENT->value,
            'created_by' => $this->admin->id,
        ]);

        $product2 = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state' => ProductState::VENDU->value,
            'location' => ProductLocation::CHEZ_CLIENT->value,
            'created_by' => $this->admin->id,
        ]);

        Sale::factory()->create([
            'product_id' => $product1->id,
            'is_confirmed' => true,
            'prix_vente' => 650000,
            'prix_achat_produit' => 500000,
            'date_vente_effective' => now(),
            'sold_by' => $this->admin->id,
        ]);

        Sale::factory()->create([
            'product_id' => $product2->id,
            'is_confirmed' => true,
            'prix_vente' => 450000,
            'prix_achat_produit' => 350000,
            'date_vente_effective' => now(),
            'sold_by' => $this->admin->id,
        ]);

        $stats = $this->saleService->getSalesStats(now()->subDay(), now()->addDay());

        $this->assertEquals(2, $stats['total_sales']);
        $this->assertEquals(1100000, $stats['total_revenue']); // 650000 + 450000
        $this->assertEquals(250000, $stats['total_profit']); // 150000 + 100000
    }

    /** @test */
    public function it_can_delete_sale_and_returns_product_to_stock(): void
    {
        // Créer un produit vendu
        $product = Product::factory()->create([
            'product_model_id' => $this->productModel->id,
            'state'            => ProductState::VENDU->value,
            'location'         => ProductLocation::CHEZ_CLIENT->value,
            'prix_achat'       => 500000,
            'prix_vente'       => 650000,
            'created_by'       => $this->admin->id,
        ]);

        // Créer la vente directe confirmée associée
        $sale = Sale::factory()->create([
            'product_id'           => $product->id,
            'is_confirmed'         => true,
            'sale_type'            => \App\Enums\SaleType::ACHAT_DIRECT->value,
            'client_name'          => 'Eric Mensah',
            'prix_vente'           => 650000,
            'prix_achat_produit'   => 500000,
            'date_vente_effective' => now(),
            'sold_by'              => $this->admin->id,
        ]);

        // Supprimer la vente avec un motif
        $this->saleService->deleteSale($sale, 'Erreur de saisie — test');

        // 1. Le produit doit être remis en stock (DISPONIBLE / BOUTIQUE)
        $product->refresh();
        $this->assertEquals(ProductState::DISPONIBLE, $product->state);
        $this->assertEquals(ProductLocation::BOUTIQUE, $product->location);

        // 2. Un mouvement ANNULATION_VENTE doit exister pour ce produit
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => \App\Enums\StockMovementType::ANNULATION_VENTE->value,
            'sale_id'    => $sale->id,
        ]);

        // 3. La vente doit être soft-deletée
        $this->assertSoftDeleted($sale);
    }
}
