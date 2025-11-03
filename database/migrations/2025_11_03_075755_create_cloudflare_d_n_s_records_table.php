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
        Schema::create('cloudflare_d_n_s_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_id')->unique();
            $table->string('zone_id');
            $table->string('type');
            $table->string('name');
            $table->text('content');
            $table->integer('ttl')->default(1);
            $table->boolean('proxied')->default(false);
            $table->integer('priority')->nullable();
            $table->timestamps();

            $table->index('record_id');
            $table->index('zone_id');
            $table->index(['zone_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_d_n_s_records');
    }
};
