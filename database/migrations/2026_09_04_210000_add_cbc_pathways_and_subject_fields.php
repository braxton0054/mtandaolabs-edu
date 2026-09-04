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
        Schema::create('pathways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('pathway_id')->nullable()->after('my_class_id')->constrained()->nullOnDelete();
            $table->boolean('is_compulsory')->default(true)->after('pathway_id');
            $table->boolean('is_examinable')->default(true)->after('is_compulsory');
        });

        Schema::create('student_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['subject_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject');

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pathway_id');
            $table->dropColumn(['is_compulsory', 'is_examinable']);
        });

        Schema::dropIfExists('pathways');
    }
};
