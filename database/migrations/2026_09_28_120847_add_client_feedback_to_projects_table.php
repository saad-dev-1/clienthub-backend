<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('client_feedback')->nullable()->after('is_public');
            $table->boolean('client_approved')->nullable()->after('client_feedback');
            $table->timestamp('client_feedback_at')->nullable()->after('client_approved');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['client_feedback', 'client_approved', 'client_feedback_at']);
        });
    }
};