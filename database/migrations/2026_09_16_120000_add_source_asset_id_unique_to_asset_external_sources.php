<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_external_sources', function (Blueprint $table) {
            $table->unique(['source', 'asset_id'], 'aes_source_asset_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('asset_external_sources', function (Blueprint $table) {
            $table->dropUnique('aes_source_asset_id_unique');
        });
    }
};
