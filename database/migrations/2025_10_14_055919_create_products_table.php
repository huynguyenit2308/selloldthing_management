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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('price', 12, 2);
            $table->decimal('original_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'needs_repair'])->nullable();
            $table->string('location')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_method')->nullable();
            $table->string('sku')->nullable();
            $table->string('origin')->nullable();
            $table->string('warranty')->nullable();
            $table->text('attachments')->nullable();
            $table->text('additional_info')->nullable();
            $table->enum('status', ['pending', 'published', 'hidden', 'sold'])->default('pending');
            $table->unsignedInteger('view_count')->default(0);
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
