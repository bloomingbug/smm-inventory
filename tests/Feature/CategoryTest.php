<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private Role $role;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'categories.index', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'categories.create', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'categories.edit', 'guard_name' => 'web']));
        array_push($this->permissions, Permission::create(['name' => 'categories.delete', 'guard_name' => 'web']));

        $this->role = Role::create(['name' => 'admin']);

        $this->role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_category()
    {
        $response = $this->actingAs($this->user)->get(route('apps.categories.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Categories/Index')
                ->has('categories')
        );
    }

    public function test_can_search_category()
    {
        $response = $this->actingAs($this->user)->get(route('apps.categories.index', ['q' => 'cari jodoh']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Categories/Index')
                ->has('categories')
        );
    }

    public function test_cant_view_page_category_when_unauthenticated()
    {
        $response = $this->get(route('apps.categories.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_category_when_user_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.categories.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_create_category()
    {
        $response = $this->actingAs($this->user)->get(route('apps.categories.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Categories/Create'));
    }

    public function test_cant_view_page_create_category_when_unauthenticated()
    {
        $response = $this->get(route('apps.categories.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_create_category_when_user_dont_have_permission()
    {
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.categories.create'));
        $response->assertStatus(403);
    }

    public function test_can_create_category()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $response = $this->actingAs($this->user)->post(route('apps.categories.store'), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.categories.index'));
    }

    public function test_cant_create_category_when_unauthenticated()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $response = $this->post(route('apps.categories.store'), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_create_category_when_user_dont_have_permission()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $response = $this->actingAs($this->userNotHavePermissions)->post(route('apps.categories.store'), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_create_category_when_blank_input()
    {
        $response = $this->actingAs($this->user)->post(route('apps.categories.store'), [
            'name' => '',
            'description' => '',
            'image' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'description', 'image']);
    }

    public function test_cant_create_category_when_image_not_image()
    {
        $response = $this->actingAs($this->user)->post(route('apps.categories.store'), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => 'not-image',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
    }

    public function test_can_view_page_edit_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->get(route('apps.categories.edit', $category->id));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Apps/Categories/Edit')->has('category'));
    }

    public function test_cant_view_page_edit_category_when_unauthenticated()
    {
        $category = Category::factory()->create();

        $response = $this->get(route('apps.categories.edit', $category->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_view_page_edit_category_when_user_dont_have_permission()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.categories.edit', $category->id));
        $response->assertStatus(403);
    }

    public function test_cant_view_page_edit_category_when_not_found()
    {
        $response = $this->actingAs($this->user)->get(route('apps.categories.edit', 9999));
        $response->assertStatus(404);
    }

    public function test_can_update_category()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.categories.update', $category->id), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.categories.index'));
    }

    public function test_cant_update_category_when_unauthenticated()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $category = Category::factory()->create();

        $response = $this->put(route('apps.categories.update', $category->id), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_update_category_when_user_dont_have_permission()
    {
        Storage::fake('categories');
        $image = UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048);

        $category = Category::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->put(route('apps.categories.update', $category->id), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => $image,
        ]);

        $response->assertStatus(403);
    }

    public function test_cant_update_category_when_blank_input()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.categories.update', $category->id), [
            'name' => '',
            'description' => '',
            'image' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'description']);
    }

    public function test_cant_update_category_when_image_not_image()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->put(route('apps.categories.update', $category->id), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => 'not-image',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
    }

    public function test_cant_update_when_not_found()
    {
        $response = $this->actingAs($this->user)->put(route('apps.categories.update', 9999), [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'image' => UploadedFile::fake()->image('category.jpg', 100, 100)->size(2048),
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('apps.categories.destroy', $category->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.categories.index'));
        $category = $category->find($category->id);
        $this->assertNull($category);
    }

    public function test_cant_delete_category_when_unauthenticated()
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('apps.categories.destroy', $category->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_cant_delete_category_when_user_dont_have_permission()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->userNotHavePermissions)->delete(route('apps.categories.destroy', $category->id));

        $response->assertStatus(403);
    }

    public function test_cant_delete_when_not_found()
    {
        $response = $this->actingAs($this->user)->delete(route('apps.categories.destroy', 9999));

        $response->assertStatus(404);
    }
}
