<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('input_name');
            $table->string('output_name');
            $table->string('input_file_id')->nullable();
            $table->string('output_file_id')->nullable();
            $table->string('time_period')->nullable();
            $table->string('user_name');
            $table->timestamp('date');
            $table->string('case_number')->nullable();
            $table->longText('notes')->nullable();
            $table->longText('input_url')->nullable();
            $table->longText('output_url')->nullable();
            $table->integer('page_size')->nullable();
            $table->string('status');
            $table->boolean('flag')->default(0);
            $table->string('share_with')->nullable();
            $table->string('share_type')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
}
