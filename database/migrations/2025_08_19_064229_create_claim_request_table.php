<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claim_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('message');
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->constrained('user')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignUuid('post_id')->constrained('post')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_request');
    }
};
