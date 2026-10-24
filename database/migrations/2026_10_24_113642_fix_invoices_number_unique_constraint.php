<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Purana global unique drop karein
            $table->dropUnique('invoices_number_unique');
            
            // Naya composite unique add karein (user_id + number)
            $table->unique(['user_id', 'number'], 'invoices_user_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_user_number_unique');
            $table->unique('number', 'invoices_number_unique');
        });
    }
};