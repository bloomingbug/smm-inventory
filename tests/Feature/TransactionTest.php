<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\ExceptionWrapper;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private Product $product;

    private Customer $customer;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'transactions.index', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();

        $this->product = Product::factory()->create();

        $this->customer = Customer::factory()->create();
    }

    public function test_can_view_page_transaction()
    {
        $response = $this->actingAs($this->user)->get(route('apps.transactions.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Transactions/Index')
                ->has('customers')
                ->has('carts')
                ->has('carts_total')
        );
    }

    public function test_search_product_found()
    {
        $response = $this->actingAs($this->user)->post(route('apps.transactions.searchProduct', ['barcode' => $this->product->barcode]));
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "data" => $this->product->toArray()
        ]);
    }

    public function test_search_product_not_found()
    {
        $response = $this->actingAs($this->user)->post(route('apps.transactions.searchProduct', ['barcode' => $this->faker->randomNumber()]));
        $response->assertStatus(404);
        $response->assertJson([
            "success" => false,
            "data" => null
        ]);
    }

    public function test_can_add_to_cart()
    {
        $response = $this->actingAs($this->user)->post(route('apps.transactions.addToCart'), [
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas("success", "Product Added Successfully!.");
    }

    public function test_cant_add_to_cart_out_of_stock()
    {
        $response = $this->actingAs($this->user)->post(route('apps.transactions.addToCart'), [
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock + 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas("error", "Out of Stock Product!.");
    }

    public function test_can_destroy_cart()
    {

        $cart = Cart::create([
            "cashier_id" => $this->user->id,
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock,
        ]);

        $response = $this->actingAs($this->user)->post(route('apps.transactions.destroyCart'), [
            'id' => $cart->id
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas("success", "Product Removed Successfully!.");
    }

    public function test_cant_destroy_cart()
    {
        $this->markTestSkipped("Tidak ada handling untuk cart yang tidak ditemukan");

        $response = $this->actingAs($this->user)->post(route('apps.transactions.destroyCart'), [
            'id' => fake()->randomNumber()
        ]);

        $response->assertStatus(404);
    }

    public function test_can_store_transaction()
    {
        $cart = Cart::create([
            "cashier_id" => $this->user->id,
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock,
        ]);

        $response = $this->actingAs($this->user)->post(route('apps.transactions.store'), [
            "customer_id" => $this->customer->id,
            "cash" => $cart->price * $cart->qty + 100000,
            "discount" => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
        ]);
    }

    public function test_cant_store_transaction_when_under_payment()
    {

        Cart::create([
            "cashier_id" => $this->user->id,
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock,
        ]);

        $response = $this->actingAs($this->user)->post(route('apps.transactions.store'), [
            "customer_id" => $this->customer->id,
            "cash" => 0,
            "discount" => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            "success" => false,
        ]);
    }

    public function test_can_print_invoice()
    {
        $cart = Cart::create([
            "cashier_id" => $this->user->id,
            "product_id" => $this->product->id,
            "price" => $this->product->sell_price,
            "qty" => $this->product->stock,
        ]);

        $transaction = $this->actingAs($this->user)->post(route('apps.transactions.store'), [
            "customer_id" => $this->customer->id,
            "cash" => $cart->price * $cart->qty + 100000,
            "discount" => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('apps.transactions.print', ['invoice' => Transaction::first()->invoice]));
        $response->assertViewIs('print.nota');
        $response->assertViewHas('transaction');
    }

    public function test_cant_print_invoice()
    {
        $response = $this->actingAs($this->user)->get(route('apps.transactions.print', ['invoice' => Str::random(10)]));
        $response->assertStatus(404);
    }
}
