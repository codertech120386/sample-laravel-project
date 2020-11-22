<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkspacePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workspace_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('space_type', 50);
            $table->string('title', 50);
            $table->text('sub_title');
            $table->unsignedInteger('duration');
            $table->unsignedInteger('cost');
            $table->string('location_type', 50)->default('Single Location');
            $table->timestamps();

            $table->unique(['workspace_id', 'space_type', 'title', 'duration']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workspace_plans');
    }
}
