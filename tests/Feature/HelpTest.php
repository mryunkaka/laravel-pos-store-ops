<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use DatabaseTransactions;

    public function test_help_page_is_displayed_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/help');

        $response->assertOk();
        $response->assertViewIs('help.index');
    }

    public function test_help_page_requires_authentication(): void
    {
        $response = $this->get('/help');

        $response->assertRedirect('/login');
    }

    public function test_help_page_displays_expected_content(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/help');

        $response->assertOk();
        $response->assertSee('Bantuan', false);
        $response->assertSee('Dokumentasi', false);
        $response->assertSee('Tentang POSDash', false);
        $response->assertSee('Point of Sale', false);
        $response->assertSee('Produk', false);
        $response->assertSee('Inventaris', false);
        $response->assertSee('Pengelolaan Pesanan', false);
        $response->assertSee('Stack Teknis', false);
        $response->assertSee('Tips Cepat', false);
    }
}
