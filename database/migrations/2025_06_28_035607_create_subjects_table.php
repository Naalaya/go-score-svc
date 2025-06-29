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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique()->comment('Subject code: toan, ngu_van, etc.');
            $table->string('display_name', 100)->comment('Display name in Vietnamese');

            $table->string('group_code', 5)->nullable()->comment('Exam group: A, B, C, D');

            $table->unsignedSmallInteger('order')->default(999)->comment('Display order');
            $table->boolean('is_active')->default(true)->comment('Subject status');

            $table->timestamps();

            $table->index('group_code', 'idx_subjects_group');
            $table->index(['is_active', 'order'], 'idx_subjects_active_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
