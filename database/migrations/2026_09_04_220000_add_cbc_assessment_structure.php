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
        Schema::create('competency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_score');
            $table->unsignedTinyInteger('max_score');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('grade_systems', function (Blueprint $table) {
            $table->foreignId('competency_level_id')->nullable()->after('class_group_id')->constrained()->nullOnDelete();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->string('assessment_type')->default('school_based')->after('publish_result');
            $table->unsignedSmallInteger('weight_percent')->nullable()->after('assessment_type');
        });

        Schema::table('exam_slots', function (Blueprint $table) {
            $table->string('strand')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_slots', function (Blueprint $table) {
            $table->dropColumn('strand');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['assessment_type', 'weight_percent']);
        });

        Schema::table('grade_systems', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competency_level_id');
        });

        Schema::dropIfExists('competency_levels');
    }
};
