<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('group_admin')->after('email_verified_at');
            $table->foreignId('group_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->boolean('is_active')->default(true)->after('must_change_password');
            $table->index('role');
            $table->index('is_active');
        });

        DB::table('users')->update([
            'role' => 'saas_admin',
            'group_id' => null,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn(['role', 'must_change_password', 'is_active']);
        });
    }
};
