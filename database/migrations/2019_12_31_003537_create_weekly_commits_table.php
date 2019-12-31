<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyCommitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_commits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('repository_id')->references('id')->on('repositories');
            $table->unsignedInteger('author_id')->references('id')->on('authors');
            $table->timestamp('week');
            $table->unsignedInteger('additions');
            $table->unsignedInteger('deletions');
            $table->unsignedInteger('commits');
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
        Schema::dropIfExists('weekly_commits');
    }
}
