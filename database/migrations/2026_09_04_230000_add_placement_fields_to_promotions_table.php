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
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('pathway_id')->nullable()->after('school_id')->constrained()->nullOnDelete();
            $table->json('elective_subject_ids')->nullable()->after('pathway_id');
            $table->decimal('placement_score', 5, 2)->nullable()->after('elective_subject_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pathway_id');
            $table->dropColumn(['elective_subject_ids', 'placement_score']);
        });
    }
};
