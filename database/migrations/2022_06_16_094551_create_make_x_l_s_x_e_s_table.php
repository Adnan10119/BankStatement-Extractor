<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMakeXLSXESTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('make_x_l_s_x_e_s', function (Blueprint $table) {
            $table->id();
            $table->string('index');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_type');
            $table->string('ck');
            $table->string('date');
            $table->longText('description');
            $table->string('debit');
            $table->string('credit');
            $table->string('value');
            $table->string('balance');
            $table->string('statement_balance');
            $table->string('difference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('make_x_l_s_x_e_s');
    }
}
