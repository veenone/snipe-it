<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adapter TYPE discovery moved to the glob-based
 * SyncAdapter::allTypes() at runtime, so we no longer pre-populate a
 * sync_adapter_instances row per shipped adapter. This migration clears
 * out the untouched seed rows left by the earlier seed migrations and
 * drops the now-vestigial built_in column. Configured rows (any row
 * with associated sync_adapter_settings) are preserved and become
 * normal admin-owned instances.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sync_adapter_instances')
            ->where('built_in', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sync_adapter_settings')
                    ->whereColumn('sync_adapter_settings.sync_adapter_instance_id', 'sync_adapter_instances.id');
            })
            ->delete();

        Schema::table('sync_adapter_instances', function (Blueprint $table) {
            $table->dropColumn('built_in');
        });
    }

    public function down(): void
    {
        Schema::table('sync_adapter_instances', function (Blueprint $table) {
            $table->boolean('built_in')->default(false)->after('active');
        });
    }
};
