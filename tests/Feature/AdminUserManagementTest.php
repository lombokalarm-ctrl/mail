<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_saas_admin_can_create_group_admin_user(): void
    {
        $saasAdmin = User::factory()->saasAdmin()->create();

        $this->actingAs($saasAdmin)
            ->post(route('admin.users.store'), [
                'group_name' => 'Acme Travel',
                'viewer_token' => 'acmetravel2026',
                'group_status' => 'active',
                'name' => 'Admin Acme',
                'email' => 'admin-acme@example.com',
                'role' => User::ROLE_GROUP_ADMIN,
                'password' => 'TempPass123!',
                'password_confirmation' => 'TempPass123!',
                'must_change_password' => '1',
            ])
            ->assertRedirect();

        $group = Group::query()->where('viewer_token', 'acmetravel2026')->firstOrFail();
        $user = User::query()->where('email', 'admin-acme@example.com')->firstOrFail();

        $this->assertSame('Acme Travel', $group->name);
        $this->assertSame('active', $group->status);
        $this->assertSame(User::ROLE_GROUP_ADMIN, $user->role);
        $this->assertSame($group->id, $user->group_id);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('TempPass123!', $user->password));
    }

    public function test_saas_admin_can_reset_group_admin_password(): void
    {
        $saasAdmin = User::factory()->saasAdmin()->create();
        $groupAdmin = User::factory()->groupAdmin()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($saasAdmin)
            ->put(route('admin.users.reset-password', $groupAdmin), [
                'password' => 'ResetPass123!',
                'password_confirmation' => 'ResetPass123!',
            ])
            ->assertRedirect();

        $groupAdmin->refresh();

        $this->assertTrue($groupAdmin->must_change_password);
        $this->assertTrue(Hash::check('ResetPass123!', $groupAdmin->password));
    }

    public function test_saas_admin_can_update_group_admin_status(): void
    {
        $saasAdmin = User::factory()->saasAdmin()->create();
        $firstGroup = Group::factory()->create();
        $secondGroup = Group::factory()->create();
        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'email' => 'admin-group@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($saasAdmin)
            ->patch(route('admin.users.update', $groupAdmin), [
                'name' => 'Admin Baru',
                'email' => 'admin-group@example.com',
                'role' => User::ROLE_GROUP_ADMIN,
                'group_id' => $secondGroup->id,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $groupAdmin->id,
            'name' => 'Admin Baru',
            'group_id' => $secondGroup->id,
            'is_active' => false,
        ]);
    }

    public function test_saas_admin_dashboard_hides_global_inbox_panels(): void
    {
        $saasAdmin = User::factory()->saasAdmin()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($saasAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Customer Terbaru')
            ->assertSee('User Manager')
            ->assertDontSee('Inbox Manager')
            ->assertDontSee('Inbox Terbaru');
    }
}
