<?php

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
        //
        if (!Schema::hasTable('custom_consumable_fieldsets')) {
        Schema::create('custom_consumable_fieldsets', function (Blueprint $table) { 
            $table->id(); // Equivalent to $table->bigIncrements('id') 
            $table->string('name', 191); 
            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns 
            $table->integer('created_by')->nullable();
        });
        }
     
        if (Schema::hasTable('custom_fields')) {
     
        Schema::table('custom_fields', function (Blueprint $table) { 
            $table->integer('field_category')->default(0); 
        });
    }

    
        Schema::table('custom_field_custom_fieldset', function (Blueprint $table) { 
            $table->integer('field_category')->default(0); 
        });
        
    
        Schema::table('categories', function (Blueprint $table) { 
            $table->integer('fieldset_id')->nullable(); 
        });
    
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_consumable_fieldsets');
        Schema::table('custom_fields', function (Blueprint $table) { 
            $table->dropColumn('field_category');
        });
        // Schema::table('custom_fields_custom_fieldset', function (Blueprint $table) { 
        //     $table->dropColumn('field_category');
        // });
        Schema::table('categories', function (Blueprint $table) { 
            $table->dropColumn('fieldset_id');
        });
    }
};
