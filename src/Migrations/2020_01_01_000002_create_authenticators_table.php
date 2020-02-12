<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAuthenticatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flashpoint_authenticators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->unique();
            $table->boolean('is_user');
            $table->string('password')->nullable();
            $table->json('permissions')->nullable();

            $table->timestamp('locked_at')->nullable();
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
        Schema::dropIfExists('flashpoint_authenticators');
    }
}
