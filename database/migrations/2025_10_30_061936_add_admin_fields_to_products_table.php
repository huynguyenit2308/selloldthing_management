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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status')->comment('Ghim sản phẩm nổi bật');
            $table->boolean('is_approved')->default(false)->after('is_featured')->comment('Đã duyệt bởi admin');
            $table->string('rejection_reason')->nullable()->after('is_approved')->comment('Lý do từ chối');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason')->comment('Thời gian duyệt');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null')->comment('Admin duyệt');
            $table->timestamp('featured_until')->nullable()->after('approved_by')->comment('Ghim nổi bật đến');
            $table->timestamp('expires_at')->nullable()->after('featured_until')->comment('Hết hạn hiển thị');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'is_featured',
                'is_approved',
                'rejection_reason',
                'approved_at',
                'approved_by',
                'featured_until',
                'expires_at'
            ]);
        });
    }
};
