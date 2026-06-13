<?php

use App\Models\Group;
use App\Models\Inbox;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inboxes', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Inbox::query()->orderBy('id')->each(function (Inbox $inbox): void {
            $group = Group::query()->create([
                'name' => $inbox->inbox_name,
                'viewer_token' => $inbox->getRawOriginal('access_token'),
                'status' => 'active',
            ]);

            $inbox->forceFill(['group_id' => $group->id])->save();
        });

        Schema::table('inboxes', function (Blueprint $table) {
            $table->unique('inbox_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inboxes', function (Blueprint $table) {
            $table->dropUnique(['inbox_name']);
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
