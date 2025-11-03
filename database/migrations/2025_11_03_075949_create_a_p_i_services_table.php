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
        Schema::create('a_p_i_services', function (Blueprint $table) {
            $table->id();
            $table->string('kong_service_id')->unique();
            $table->string('name');
            $table->string('url');
            $table->json('routes')->nullable();
            $table->json('plugins')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('kong_service_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_p_i_services');
    }
};
