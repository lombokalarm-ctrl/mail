<?php

namespace Tests\Feature;

use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_group_and_add_inbox_with_manual_token(): void
    {
        $user = User::factory()->create();

        $groupResponse = $this->actingAs($user)->postJson('/api/groups', [
            'name' => 'Acme Travel',
            'viewer_token' => 'acme2026',
            'status' => 'active',
        ]);

        $groupResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Acme Travel')
            ->assertJsonPath('data.viewer_token', 'acme2026');

        $groupId = $groupResponse->json('data.id');

        $inboxResponse = $this->actingAs($user)->postJson("/api/groups/{$groupId}/inboxes", [
            'inbox_name' => 'support-acme',
        ]);

        $inboxResponse
            ->assertCreated()
            ->assertJsonPath('data.inbox_name', 'support-acme')
            ->assertJsonPath('data.viewer_url', '/view/support-acme-acme2026');

        $this->assertDatabaseHas('groups', [
            'id' => $groupId,
            'viewer_token' => 'acme2026',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $groupId,
            'inbox_name' => 'support-acme',
            'slug' => 'support-acme',
        ]);
    }

    public function test_updating_group_token_changes_viewer_url_for_existing_inbox(): void
    {
        $user = User::factory()->create();

        $group = \App\Models\Group::factory()->create([
            'viewer_token' => 'oldtoken',
        ]);

        $inbox = Inbox::factory()->create([
            'group_id' => $group->id,
            'inbox_name' => 'sales-acme',
            'slug' => 'sales-acme',
        ]);

        $this->actingAs($user)->patchJson("/api/groups/{$group->id}", [
            'viewer_token' => 'newtoken',
        ])->assertOk();

        $this->actingAs($user)->getJson("/api/inboxes/{$inbox->id}")
            ->assertOk()
            ->assertJsonPath('data.access_token', 'newtoken')
            ->assertJsonPath('data.viewer_url', '/view/sales-acme-newtoken');
    }
}
