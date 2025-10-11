<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_id')->references('id')->on('users');
            $table->foreignId('rated_user_id')->references('id')->on('users');
            $table->unsignedTinyInteger('score')->comment('1から5の評価点');
            $table->text('comment')->nullable();
            $table->timestamps();

            // 同じ取引に対して同じユーザーが2度評価できないようにする
            $table->unique(['transaction_id', 'rater_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
