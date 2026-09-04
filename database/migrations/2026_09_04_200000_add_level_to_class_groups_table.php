<?php

use App\Services\Cbc\CbcCutoverService;
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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->string('level')->nullable()->after('name');
        });

        app(CbcCutoverService::class)->run();
    }

    /**
     * Reverse the migrations.
     *
     * This drops the column only. The class regrouping performed by
     * CbcCutoverService is a one-way data cutover and is not reversed.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
