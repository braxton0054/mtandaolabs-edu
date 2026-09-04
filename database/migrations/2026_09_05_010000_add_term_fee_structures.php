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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('my_class_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('fee_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('amount');
            $table->timestamps();
            $table->unique(['school_id', 'my_class_id', 'semester_id', 'fee_id'], 'fee_structures_unique');
        });

        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('user_id')->constrained()->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('semester_id');
        });

        Schema::dropIfExists('fee_structures');
    }
};
