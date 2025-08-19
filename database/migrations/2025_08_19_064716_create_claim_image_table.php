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
        Schema::create('claim_image', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url', 500);
            $table->text('caption');
            $table->foreignUuid('claim_request_id')->constrained('claim_request')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_image');
    }
};
