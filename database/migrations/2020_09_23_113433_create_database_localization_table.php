<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseLocalizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('database_localization', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->string('group')->default('*'); 
            $table->string('namespace')->default('*'); 
            $table->json('text')->nullable(); 
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
        Schema::dropIfExists('database_localization');
    }
}
