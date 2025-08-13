<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_sitemap_is_accessible()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_homepage()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(url('/'));
    }

    public function test_sitemap_contains_lots()
    {
        // 創建一個用戶
        $user = User::factory()->create();

        // 創建一個已發布的拍賣品
        $lot = Lot::factory()->create([
            'owner_id' => $user->id,
            'status' => 61, // 已上架狀態
            'auction_start_at' => now(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(url('/lots/' . $lot->id));
    }

    public function test_sitemap_does_not_contain_unpublished_lots()
    {
        // 創建一個用戶
        $user = User::factory()->create();

        // 創建一個未發布的拍賣品
        $lot = Lot::factory()->create([
            'owner_id' => $user->id,
            'status' => 0, // 未發布狀態
            'auction_start_at' => null,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertDontSee(url('/lots/' . $lot->id));
    }

    public function test_sitemap_contains_static_pages()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(url('/about-antiquary'));
        $response->assertSee(url('/privacy-policy'));
        $response->assertSee(url('/terms'));
    }
}
