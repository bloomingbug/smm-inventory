<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        array_push($this->permissions, Permission::create(['name' => 'dashboard.index', 'guard_name' => 'web']));

        $role = Role::create(['name' => 'admin']);

        $role->givePermissionTo($this->permissions);

        $this->user = User::factory()->create();

        $this->user->assignRole('admin');
    }

    public function test_can_view_page_login()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/Login'));
    }

    public function test_can_login()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('apps.dashboard'));
    }

    public function test_cant_login_when_blank_input()
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_cant_login_when_not_registered()
    {
        $response = $this->post(route('login'), [
            'email' => 'notregistered@email.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_cant_login_when_wrong_password()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_lock_when_brute_force_login()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(302);

        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(302);

        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(302);

        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(302);

        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(302);

        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        // 429 = TooManyRequest
        $response->assertStatus(429);
    }

    public function test_can_logout()
    {
        $response = $this->actingAs($this->user)->post(route('logout'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
