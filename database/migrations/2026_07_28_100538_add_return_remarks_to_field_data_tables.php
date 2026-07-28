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
        Schema::table('monthly_harvests', function (Blueprint $table) {
            $table->text('return_remarks')->nullable()->after('status');
        });
        
        Schema::table('pollen_productions', function (Blueprint $table) {
            $table->text('return_remarks')->nullable()->after('status');
        });
        
        Schema::table('nursery_operations', function (Blueprint $table) {
            $table->text('return_remarks')->nullable()->after('status');
        });
        
        Schema::table('hybrid_distributions', function (Blueprint $table) {
            $table->text('return_remarks')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_harvests', function (Blueprint $table) {
            $table->dropColumn('return_remarks');
        });
        
        Schema::table('pollen_productions', function (Blueprint $table) {
            $table->dropColumn('return_remarks');
        });
        
        Schema::table('nursery_operations', function (Blueprint $table) {
            $table->dropColumn('return_remarks');
        });
        
        Schema::table('hybrid_distributions', function (Blueprint $table) {
            $table->dropColumn('return_remarks');
        });
    }
};
