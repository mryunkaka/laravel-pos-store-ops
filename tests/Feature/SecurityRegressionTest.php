<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_state_changing_routes_do_not_use_get(): void
    {
        $this->assertSame(['DELETE'], app('router')->getRoutes()->getByName('pos.deleteCart')->methods());
        $this->assertSame(['POST'], app('router')->getRoutes()->getByName('backup.create')->methods());
        $this->assertSame(['DELETE'], app('router')->getRoutes()->getByName('backup.delete')->methods());
    }

    public function test_invalid_public_invoice_token_returns_not_found(): void
    {
        $this->get('/e-invoice-mobile/not-a-valid-token')->assertNotFound();
    }

    public function test_invoice_pdf_get_does_not_generate_or_update_missing_invoice(): void
    {
        Permission::firstOrCreate(['name' => 'orders.menu'], ['group_name' => 'orders']);
        $user = User::factory()->create();
        $user->givePermissionTo('orders.menu');
        $customer = Customer::factory()->create();
        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'invoice_no' => 'QA-PDF-'.uniqid(),
            'order_date' => now(),
            'order_status' => 'complete',
            'total_products' => 1,
            'sub_total' => 100000,
            'vat' => 0,
            'total' => 100000,
            'pay_amount' => 100000,
            'due_amount' => 0,
            'invoice_pdf_path' => null,
            'invoice_upload_status' => null,
        ]);

        $this->actingAs($user)
            ->get(route('order.invoicePdf', $order->id))
            ->assertRedirect(route('order.orderDetails', $order->id));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'invoice_pdf_path' => null,
            'invoice_upload_status' => null,
        ]);
    }
}
