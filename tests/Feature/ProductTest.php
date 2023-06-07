<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private Category $category;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'products.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'products.create', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'products.edit', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'products.delete', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();

        $this->category = Category::factory()->create();
    }

    public function test_can_view_page_product()
    {
        $response = $this->actingAs($this->user)->get(route('apps.products.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Products/Index')
                ->has('products')
        );
    }

    public function test_can_search_product()
    {
        $response = $this->actingAs($this->user)->get(route('apps.products.index', ['q' => 'cari jodoh']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Products/Index')
                ->has('products')
        );
    }

    public function test_cant_view_page_product_when_unauthenticated()
    {
        $response = $this->get(route('apps.products.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_product_when_user_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.products.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_create_product()
    {
        $response = $this->actingAs($this->user)->get(route('apps.products.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Products/Create')->has('categories'));
    }

    public function test_cant_view_page_create_product_when_unauthenticated()
    {
        $response = $this->get(route('apps.products.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_create_product_when_user_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.products.create'));
        $response->assertStatus(403);
    }

    public function test_can_create_product()
    {
        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->actingAs($this->user)->post(route('apps.products.store'), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.products.index'));
    }

    public function test_cant_create_product_when_unauthenticated()
    {
        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->post(route('apps.products.store'), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_create_product_when_user_dont_have_permission()
    {
        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->actingAs($this->userNotHavePermissions)->post(route('apps.products.store'), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_create_product_when_blank_input()
    {
        $response = $this->actingAs($this->user)->post(route('apps.products.store'), [
            'image' => '',
            'barcode' => '',
            'title' => '',
            'description' => '',
            'category_id' => '',
            'buy_price' => '',
            'sell_price' => '',
            'stock' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'image',
            'barcode',
            'title',
            'description',
            'category_id',
            'buy_price',
            'sell_price',
            'stock',
        ]);
    }

    public function test_can_view_page_edit_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->get(route('apps.products.edit', $product->id));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Products/Edit')->has('product')->has('categories'));
    }

    public function test_cant_view_page_edit_product_when_unauthenticated()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('apps.products.edit', $product->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_edit_product_when_user_dont_have_permission()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.products.edit', $product->id));
        $response->assertStatus(403);
    }

    public function test_cant_view_page_edit_product_when_product_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.products.edit', 9999));
        $response->assertStatus(404);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->actingAs($this->user)->put(route('apps.products.update', $product->id), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.products.index'));
    }

    public function test_cant_update_product_when_unauthenticated()
    {
        $product = Product::factory()->create();

        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->put(route('apps.products.update', $product->id), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_update_product_when_user_dont_have_permission()
    {
        $product = Product::factory()->create();

        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        $response = $this->actingAs($this->userNotHavePermissions)->put(route('apps.products.update', $product->id), [
            'image' => $image,
            'barcode' => fake()->unique()->isbn10(),
            'title' => $this->faker->name,
            'description' => $this->faker->text,
            'category_id' => $this->category->id,
            'buy_price' => $this->faker->numberBetween(1000, 100000),
            'sell_price' => $this->faker->numberBetween(1000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_update_product_when_blank_input()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.products.update', $product->id), [
            'image' => '',
            'barcode' => '',
            'title' => '',
            'description' => '',
            'category_id' => '',
            'buy_price' => '',
            'sell_price' => '',
            'stock' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'barcode',
            'title',
            'description',
            'category_id',
            'buy_price',
            'sell_price',
            'stock',
        ]);
    }

    public function test_cant_update_product_when_product_not_found()
    {
        $response = $this->actingAs($this->user)->put(route('apps.products.update', 9999), [
            'image' => '',
            'barcode' => '',
            'title' => '',
            'description' => '',
            'category_id' => '',
            'buy_price' => '',
            'sell_price' => '',
            'stock' => '',
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('apps.products.destroy', $product->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('apps.products.index'));
        $product = Product::find($product->id);
        $this->assertNull($product);
    }

    public function test_cant_delete_product_when_unauthenticated()
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('apps.products.destroy', $product->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_delete_product_when_user_dont_have_permission()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->delete(route('apps.products.destroy', $product->id));
        $response->assertStatus(403);
    }

    public function test_cant_delete_product_when_product_not_found()
    {
        $response = $this->actingAs($this->user)->delete(route('apps.products.destroy', 9999));
        $response->assertStatus(404);
    }
}
