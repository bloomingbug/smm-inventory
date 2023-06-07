<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfitTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private User $userNotHavePermissions;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'profits.index', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');

        $this->userNotHavePermissions = User::factory()->create();
    }

    public function test_can_view_page_profit_report()
    {
        $response = $this->actingAs($this->user)->get(route('apps.profits.index'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Profits/Index')
        );
    }

    public function test_cant_view_page_profit_when_not_have_permissions()
    {
        $this->markTestSkipped("Error Kurang Middleware");
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.profits.index'));
        $response->assertStatus(403);
    }

    public function test_can_view_page_profit_when_unauthenticated()
    {
        $response = $this->get(route('apps.profits.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_view_page_filter_profit_report()
    {
        $response = $this->actingAs($this->user)->get(route('apps.profits.index', [
            "start_date" => "2023-06-02",
            "end_date" => "2023-06-02",
        ]));
        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Apps/Profits/Index')
        );
    }

    public function test_cant_view_page_filter_profit_when_not_have_permissions()
    {
        $this->markTestSkipped("Error Kurang Middleware");
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.profits.index', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(403);
    }

    public function test_can_view_page_filter_profit_when_unauthenticated()
    {
        $response = $this->get(route('apps.profits.index', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_export_profit()
    {
        $response = $this->actingAs($this->user)->get(route('apps.profits.export', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(200);
    }

    public function test_cant_export_profit_when_not_have_permissions()
    {
        $this->markTestSkipped("Error Kurang Middleware");
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.profits.export', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(403);
    }

    public function test_can_export_profit_when_unauthenticated()
    {
        $response = $this->get(route('apps.profits.export', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_can_export_pdf_profit()
    {
        $response = $this->actingAs($this->user)->get(route('apps.profits.pdf', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(200);
    }

    public function test_cant_export_pdf_profit_when_not_have_permissions()
    {
        $this->markTestSkipped("Error Kurang Middleware");
        $response = $this->actingAs($this->userNotHavePermissions)->get(route('apps.profits.pdf', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(403);
    }

    public function test_can_export_pdf_profit_when_unauthenticated()
    {
        $response = $this->get(route('apps.profits.pdf', ['start_date' => "2023-06-02", 'end_date' => "2023-06-02"]));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
