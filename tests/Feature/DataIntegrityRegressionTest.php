<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataIntegrityRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_barcode_quick_add_rejects_expired_product(): void
    {
        $user = $this->userWithPermission('pos.menu');
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'code' => 'QA-EXPIRED-'.uniqid(),
            'stock' => 10,
            'expire_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->postJson(route('pos.barcode.quickAdd'), [
            'code' => $product->code,
        ]);

        $response->assertStatus(422)->assertJson([
            'success' => false,
            'message' => 'Produk sudah kadaluarsa.',
        ]);
    }

    public function test_customer_with_order_history_cannot_be_deleted(): void
    {
        $user = $this->userWithPermission('customer.menu');
        $customer = Customer::factory()->create();
        $order = Order::create([
            'customer_id' => $customer->id,
            'invoice_no' => 'QA-CUSTOMER-'.uniqid(),
            'order_date' => now(),
            'order_status' => 'complete',
            'total_products' => 1,
            'sub_total' => 100000,
            'total' => 100000,
            'payment_type' => 'Tunai Rp 100.000',
            'pay_amount' => 100000,
            'due_amount' => 0,
        ]);

        $response = $this->actingAs($user)->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    private function userWithPermission(string $permission): User
    {
        Permission::firstOrCreate(['name' => $permission], ['group_name' => 'qa']);
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }
}
