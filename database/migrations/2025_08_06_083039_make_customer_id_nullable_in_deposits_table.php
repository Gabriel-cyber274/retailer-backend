<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Drop the existing foreign key first
            $table->dropForeign(['customer_id']);
        });

        Schema::table('deposits', function (Blueprint $table) {
            // Modify the column to be nullable
            $table->foreignId('customer_id')
                ->nullable()
                ->change();
        });

        Schema::table('deposits', function (Blueprint $table) {
            // Re-apply the foreign key constraint
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['customer_id']);
        });

        Schema::table('deposits', function (Blueprint $table) {
            // Make it not nullable again
            $table->foreignId('customer_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('deposits', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
};
