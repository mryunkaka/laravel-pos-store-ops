<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permission if it doesn't exist (outside transaction)
        // Using firstOrCreate ensures it only creates if it doesn't exist
        Permission::firstOrCreate(
            ['name' => 'product.menu'],
            ['group_name' => 'product']
        );
        
        // Create a role and assign permission (outside transaction)
        $role = Role::firstOrCreate(['name' => 'test-role']);
        if (!$role->hasPermissionTo('product.menu')) {
            $role->givePermissionTo('product.menu');
        }
    }

    protected function createAuthenticatedUser(): User
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'test-role')->first();
        $user->assignRole($role);
        return $user;
    }

    protected function createCategory(): Category
    {
        return Category::factory()->create();
    }

    public function test_product_can_be_created_with_manual_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Test Product',
            'code' => 'MANUAL-CODE-123',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'code' => 'MANUAL-CODE-123',
        ]);
    }

    public function test_product_code_is_auto_generated_when_not_provided(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Auto Code Product',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product = Product::where('name', 'Auto Code Product')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->code);
        $this->assertStringStartsWith('PC', $product->code);
    }

    public function test_product_creation_fails_with_duplicate_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create first product with a code
        Product::factory()->create([
            'code' => 'DUPLICATE-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Duplicate Code Product',
            'code' => 'DUPLICATE-CODE',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_product_creation_with_valid_code_succeeds(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Valid Code Product',
            'code' => 'VALID-CODE-001',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Valid Code Product',
            'code' => 'VALID-CODE-001',
        ]);
    }

    public function test_product_code_field_is_editable_in_create_form(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/products/create');

        $response->assertOk();
        $response->assertSee('Kode Produk', false);
        $response->assertSee('Barcode Scanner', false);
        $response->assertSee('id="code"', false);
        $response->assertSee('id="barcode_scanner"', false);
    }

    public function test_product_code_can_be_updated(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'OLD-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => $product->name,
            'code' => 'NEW-CODE-123',
            'category_id' => $category->id,
            'stock' => $product->stock,
            'buying_price' => $product->buying_price,
            'selling_price' => $product->selling_price,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('NEW-CODE-123', $product->code);
    }

    public function test_product_code_update_fails_with_duplicate(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        // Create two products
        $product1 = Product::factory()->create([
            'code' => 'EXISTING-CODE',
            'category_id' => $category->id,
        ]);

        $product2 = Product::factory()->create([
            'code' => 'ORIGINAL-CODE',
            'category_id' => $category->id,
        ]);

        // Try to update product2 with product1's code
        $response = $this->actingAs($user)->put("/products/{$product2->id}", [
            'name' => $product2->name,
            'code' => 'EXISTING-CODE',
            'category_id' => $category->id,
            'stock' => $product2->stock,
            'buying_price' => $product2->buying_price,
            'selling_price' => $product2->selling_price,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_product_code_can_remain_unchanged_on_update(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'ORIGINAL-CODE',
            'category_id' => $category->id,
        ]);

        $originalCode = $product->code;

        $response = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'category_id' => $category->id,
            'stock' => $product->stock,
            'buying_price' => $product->buying_price,
            'selling_price' => $product->selling_price,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals($originalCode, $product->code);
        $this->assertEquals('Updated Product Name', $product->name);
    }

    public function test_product_code_field_is_editable_in_edit_form(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'TEST-CODE',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}/edit");

        $response->assertOk();
        $response->assertSee('Kode Produk', false);
        $response->assertSee('Barcode Scanner', false);
        $response->assertSee('value="TEST-CODE"', false);
        $response->assertSee('id="code"', false);
        $response->assertSee('id="barcode_scanner"', false);
    }

    public function test_code_max_length_validation(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $longCode = str_repeat('A', 51); // 51 characters

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Long Code Product',
            'code' => $longCode,
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_code_accepts_alphanumeric_characters(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Alphanumeric Code Product',
            'code' => 'ABC123-XYZ456',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Alphanumeric Code Product',
            'code' => 'ABC123-XYZ456',
        ]);
    }

    public function test_code_is_optional_in_create_request(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Optional Code Product',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product = Product::where('name', 'Optional Code Product')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->code); // Should be auto-generated
    }

    public function test_barcode_generation_uses_product_code(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'code' => 'BARCODE-TEST-123',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee('BARCODE-TEST-123', false);
        $response->assertSee('Kode Produk', false);
        $response->assertSee('Barcode Produk', false);
    }

    public function test_product_with_custom_code_displays_correct_barcode(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $customCode = 'CUSTOM-CODE-999';

        $product = Product::factory()->create([
            'code' => $customCode,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee($customCode, false);
        
        // Verify the product code is displayed in the readonly input
        $response->assertSee('value="' . $customCode . '"', false);
    }

    public function test_product_delete_is_permanent(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_permanent_product_delete_preserves_purchase_history(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-DELETE-TEST',
            'po_date' => now()->toDateString(),
            'status' => 'completed',
            'sub_total' => 100,
            'vat' => 0,
            'total' => 100,
        ]);
        $purchaseDetail = PurchaseOrderDetail::create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'total' => 100,
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('purchase_order_details', [
            'id' => $purchaseDetail->id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $purchaseOrder->id]);
        $this->assertDatabaseHas('product_references', [
            'id' => $product->id,
            'code' => $product->code,
        ]);
    }

    public function test_bulk_product_delete_is_permanent(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $products = Product::factory()->count(2)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->delete('/products', [
                'product_ids' => $products->pluck('id')->all(),
            ]);

        $response->assertRedirect('/products');
        $products->each(function (Product $product): void {
            $this->assertDatabaseMissing('products', ['id' => $product->id]);
            $reference = DB::table('product_references')->where('id', $product->id)->first();
            $this->assertNotNull($reference);
            $this->assertNotNull($reference->archived_at);
            $this->assertNotNull($reference->deleted_at);
        });
    }

    public function test_product_with_pending_purchase_cannot_be_deleted(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-PENDING-DELETE-TEST',
            'po_date' => now()->toDateString(),
            'status' => 'pending',
            'sub_total' => 100,
            'vat' => 0,
            'total' => 100,
        ]);
        PurchaseOrderDetail::create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'total' => 100,
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_creation_returns_validation_error_when_code_collides_after_validation(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();
        $armed = true;

        DB::listen(function (QueryExecuted $query) use (&$armed, $category): void {
            if (! $armed || ! str_contains(strtolower($query->sql), 'select count(*) as aggregate')) {
                return;
            }

            if (! in_array('RACE-CODE', $query->bindings, true)) {
                return;
            }

            $armed = false;
            Product::factory()->create([
                'code' => 'RACE-CODE',
                'category_id' => $category->id,
            ]);
        });

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($user)->post('/products', [
            'name' => 'Race Product',
            'code' => 'RACE-CODE',
            'category_id' => $category->id,
            'stock' => 10,
            'buying_price' => 100,
            'selling_price' => 150,
        ]);

        $response->assertSessionHasErrors(['code' => 'Kode produk sudah digunakan.']);
        $this->assertDatabaseMissing('products', ['name' => 'Race Product']);
    }
}
