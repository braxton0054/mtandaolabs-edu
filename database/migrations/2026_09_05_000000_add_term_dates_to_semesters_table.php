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
        Schema::table('semesters', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('name');
            $table->date('stop_date')->nullable()->after('start_date');
            $table->date('midterm_start')->nullable()->after('stop_date');
            $table->date('midterm_stop')->nullable()->after('midterm_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'stop_date', 'midterm_start', 'midterm_stop']);
        });
    }
};
