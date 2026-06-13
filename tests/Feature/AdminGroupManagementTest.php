<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_management_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
        ]);

        Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
        ]);

        $this->actingAs($user)
            ->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('Kelola Group SaaS Dan Inbox')
            ->assertSee('Acme Travel')
            ->assertSee('support-acme');
    }

    public function test_admin_can_create_group_and_inbox_from_web_ui(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.groups.store'), [
                'name' => 'Acme Travel',
                'viewer_token' => 'acme2026',
                'status' => 'active',
            ])
            ->assertRedirect();

        $group = Group::query()->where('viewer_token', 'acme2026')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.inboxes.store'), [
                'group_id' => $group->id,
                'inbox_name' => 'support-acme',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('groups', [
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
            'access_token' => 'acme2026',
        ]);
    }

    public function test_admin_can_update_and_delete_group_and_inbox_from_web_ui(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
            'status' => 'trial',
        ]);
        $otherGroup = Group::factory()->create([
            'name' => 'Beta Travel',
            'viewer_token' => 'beta2026',
        ]);
        $inbox = Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.groups.update', $group), [
                'name' => 'Acme Premium',
                'viewer_token' => 'acmepremium',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('admin.inboxes.update', $inbox), [
                'group_id' => $otherGroup->id,
                'inbox_name' => 'sales-beta',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Acme Premium',
            'viewer_token' => 'acmepremium',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'id' => $inbox->id,
            'group_id' => $otherGroup->id,
            'inbox_name' => 'sales-beta',
            'slug' => 'sales-beta',
            'access_token' => 'beta2026',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.inboxes.destroy', $inbox))
            ->assertRedirect();

        $this->actingAs($user)
            ->delete(route('admin.groups.destroy', $group))
            ->assertRedirect();

        $this->assertDatabaseMissing('inboxes', [
            'id' => $inbox->id,
        ]);

        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }
}
