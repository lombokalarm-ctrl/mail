<?php

namespace Tests\Feature;

use App\Models\Email;
use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_admin_is_redirected_to_profile_until_password_is_changed(): void
    {
        $groupAdmin = User::factory()->groupAdmin()->create([
            'password' => 'TempPass123!',
            'must_change_password' => true,
        ]);

        $this->post('/login', [
            'email' => $groupAdmin->email,
            'password' => 'TempPass123!',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($groupAdmin)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit', absolute: false));
    }

    public function test_group_admin_only_sees_inboxes_and_emails_for_own_group(): void
    {
        $firstGroup = Group::factory()->create(['name' => 'Alpha Group']);
        $secondGroup = Group::factory()->create(['name' => 'Beta Group']);
        $firstInbox = Inbox::factory()->create([
            'group_id' => $firstGroup->id,
            'inbox_name' => 'alpha-inbox',
            'slug' => 'alpha-inbox',
        ]);
        $secondInbox = Inbox::factory()->create([
            'group_id' => $secondGroup->id,
            'inbox_name' => 'beta-inbox',
            'slug' => 'beta-inbox',
        ]);

        Email::query()->create([
            'inbox_id' => $firstInbox->id,
            'sender_email' => 'sender-alpha@example.com',
            'sender_name' => 'Sender Alpha',
            'recipient_email' => 'alpha-inbox@email.apli.my.id',
            'subject' => 'Email Alpha',
            'body_text' => 'Halo Alpha',
            'received_at' => now(),
        ]);

        Email::query()->create([
            'inbox_id' => $secondInbox->id,
            'sender_email' => 'sender-beta@example.com',
            'sender_name' => 'Sender Beta',
            'recipient_email' => 'beta-inbox@email.apli.my.id',
            'subject' => 'Email Beta',
            'body_text' => 'Halo Beta',
            'received_at' => now(),
        ]);

        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($groupAdmin)
            ->get(route('admin.inboxes.index'))
            ->assertOk()
            ->assertSee('alpha-inbox')
            ->assertDontSee('beta-inbox');

        $this->actingAs($groupAdmin)
            ->get(route('admin.emails.index'))
            ->assertOk()
            ->assertSee('Email Alpha')
            ->assertDontSee('Email Beta');
    }

    public function test_group_admin_cannot_access_group_manager_or_other_group_records(): void
    {
        $firstGroup = Group::factory()->create();
        $secondGroup = Group::factory()->create();
        $firstInbox = Inbox::factory()->create([
            'group_id' => $firstGroup->id,
        ]);
        $secondInbox = Inbox::factory()->create([
            'group_id' => $secondGroup->id,
        ]);
        $groupAdmin = User::factory()->groupAdmin($firstGroup)->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($groupAdmin)
            ->get(route('admin.groups.index'))
            ->assertForbidden();

        $this->actingAs($groupAdmin)
            ->patch(route('admin.inboxes.update', $secondInbox), [
                'group_id' => $secondGroup->id,
                'inbox_name' => 'updated-beta',
            ])
            ->assertNotFound();

        $this->actingAs($groupAdmin)
            ->post(route('admin.inboxes.store'), [
                'group_id' => $secondGroup->id,
                'inbox_name' => 'new-alpha',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inboxes', [
            'group_id' => $firstGroup->id,
            'inbox_name' => 'new-alpha',
        ]);

        $this->assertDatabaseMissing('inboxes', [
            'group_id' => $secondGroup->id,
            'inbox_name' => 'new-alpha',
        ]);
    }
}
