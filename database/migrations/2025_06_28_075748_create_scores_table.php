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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();

            $table->string('sbd', 10)->unique()->comment('Student registration number');
            $table->string('ma_ngoai_ngu', 5)->nullable()->comment('Foreign language code');

            $table->decimal('toan', 4, 2)->nullable()->comment('Mathematics');
            $table->decimal('ngu_van', 4, 2)->nullable()->comment('Literature');
            $table->decimal('ngoai_ngu', 4, 2)->nullable()->comment('Foreign Language');
            $table->decimal('vat_li', 4, 2)->nullable()->comment('Physics');
            $table->decimal('hoa_hoc', 4, 2)->nullable()->comment('Chemistry');
            $table->decimal('sinh_hoc', 4, 2)->nullable()->comment('Biology');
            $table->decimal('lich_su', 4, 2)->nullable()->comment('History');
            $table->decimal('dia_li', 4, 2)->nullable()->comment('Geography');
            $table->decimal('gdcd', 4, 2)->nullable()->comment('Civic Education');

            $table->timestamps();

            $table->index(['toan', 'vat_li', 'hoa_hoc'], 'idx_scores_group_a');
            $table->index('ma_ngoai_ngu', 'idx_scores_foreign_lang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
