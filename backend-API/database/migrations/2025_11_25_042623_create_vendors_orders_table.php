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
        Schema::create('vendor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_order_id')->constrained('parent_orders');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->decimal('order_total', 20, 2);
            $table->decimal('commission_fee', 20, 2);
            $table->decimal('net_amount', 20, 2);
            $table->string('shipping_status', 50)->default('processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors_orders');
    }
};
