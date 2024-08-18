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
        Schema::create(config('otp-code.table_name'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier');
            $table->string('salt');
            $table->string('code');
            $table->integer('attempts')->default(0);
            $table->timestamp('expired_at');
            $table->timestamps();

            // Adding indexes to frequently queried columns
            $table->index(['identifier', 'salt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('otp-code.table_name'));
    }
};