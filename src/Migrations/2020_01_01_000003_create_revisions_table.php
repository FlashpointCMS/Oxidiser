<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flashpoint_revisions', function (Blueprint $table) {
            $table->bigIncrements('sequence_id');
            $table->uuid('id')->index()->default(DB::raw('uuid_generate_v4()'));
            $table->string('routing')->index();
            $table->bigInteger('previous_sequence_id')->nullable();
            $table->bigInteger('authenticator_id');

            $table->json('state')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('flashpoint_revisions');
    }
}
