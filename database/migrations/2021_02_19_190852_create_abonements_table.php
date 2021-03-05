<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbonementsTable extends Migration
{
    public function up()
    {
        Schema::create('abonements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->integer('abonement_id')->nullable();
            $table->integer('balance')->nullable();
            $table->integer('client_id')->nullable();
            $table->integer('cost')->nullable();
            $table->integer('sale')->nullable();
            $table->integer('lead_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('comment')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('company_id');
            $table->index('abonement_id');
            $table->index('balance');
            $table->index('is_active');
            $table->index('client_id');
            $table->index('cost');
            $table->index('sale');
            $table->index('lead_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('abonements');
    }
}
