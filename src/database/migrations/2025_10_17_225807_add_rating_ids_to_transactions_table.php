<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingIdsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 購入者が出品者を評価したRating ID
            $table->foreignId('buyer_rating_id')->nullable()->after('buyer_id')->constrained('ratings');

            // 出品者が購入者を評価したRating ID
            $table->foreignId('seller_rating_id')->nullable()->after('seller_id')->constrained('ratings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 外部キー制約を削除してからカラムを削除
            $table->dropConstrainedForeignId('buyer_rating_id');
            $table->dropConstrainedForeignId('seller_rating_id');
        });
    }
}