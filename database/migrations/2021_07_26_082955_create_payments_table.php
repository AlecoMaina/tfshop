<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('order_id')->index()->unsigned();
            $table->string('oid');
            $table->string('sid');
            $table->string('session_id')->nullable();
            $table->string('account');
            $table->string('transaction_amount');
            $table->string('transaction_code')->nullable();
            $table->string('telephone')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('paid_at')->nullable();
            $table->string('payment_mode')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
