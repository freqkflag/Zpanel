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
        Schema::create('cloudflare_tunnels', function (Blueprint $table) {
            $table->id();
            $table->string('tunnel_id')->unique();
            $table->string('account_id');
            $table->string('name');
            $table->text('secret')->nullable();
            $table->string('status')->default('active');
            $table->json('connections')->nullable();
            $table->timestamps();

            $table->index('tunnel_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_tunnels');
    }
};
