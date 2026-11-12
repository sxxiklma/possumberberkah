<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin_brand_test',
            'email' => 'superadmin_brand_test@sumberberkah.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_can_view_brands_index(): void
    {
        Brand::create(['name' => 'Bogasari', 'description' => 'Tepung terigu']);

        $response = $this->actingAs($this->superAdmin)->get('/master/brands');
        $response->assertStatus(200);
        $response->assertSee('Merek / Brand Produk');
        $response->assertSee('Bogasari');
    }

    public function test_can_create_brand(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/master/brands', [
            'name' => 'Pondan',
            'description' => 'Premiks kue praktis',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('brands', ['name' => 'Pondan']);
    }

    public function test_can_update_brand(): void
    {
        $brand = Brand::create(['name' => 'Old Brand', 'description' => 'Old description']);

        $response = $this->actingAs($this->superAdmin)->put("/master/brands/{$brand->id}", [
            'name' => 'Updated Brand',
            'description' => 'New description',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand',
            'description' => 'New description',
        ]);
    }

    public function test_can_delete_unused_brand(): void
    {
        $brand = Brand::create(['name' => 'Brand to delete', 'description' => 'Testing delete']);

        $response = $this->actingAs($this->superAdmin)->delete("/master/brands/{$brand->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Brand uses SoftDeletes
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_cannot_delete_brand_used_by_products(): void
    {
        $brand = Brand::create(['name' => 'Brand in use', 'description' => 'Used by product']);
        $category = Category::create(['name' => 'Cat 1', 'code' => 'CAT1', 'slug' => 'cat-1']);
        $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS']);

        Product::create([
            'sku' => 'TEST-01',
            'name' => 'Test Product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'base_unit_id' => $unit->id,
            'buy_price' => 1000,
            'selling_price' => 1500,
            'wholesale_price' => 1200,
            'min_stock' => 5,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete("/master/brands/{$brand->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'deleted_at' => null]);
    }
}
