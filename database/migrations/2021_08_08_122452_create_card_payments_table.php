<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('order_id')->index()->unsigned();
            $table->string('status');
            $table->string('transaction_amount');
            $table->string('transaction_code')->nullable();
            $table->string('telephone')->nullable();
            $table->string('name')->nullable();
            $table->dateTime('paid_at');
            $table->string('payment_mode')->deafult('Card');
            $table->boolean('payment_status')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_payments');
    }
}
