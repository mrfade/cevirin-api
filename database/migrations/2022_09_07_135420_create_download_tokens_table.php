<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('download_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('video_id')->constrained();

            $table->uuid('token')->unique();
            $table->text('url');
            $table->string('ext');

            $table->longText('headers');
            $table->ipAddress('ip');
            $table->timestamp('expires_at');

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
        Schema::dropIfExists('download_tokens');
    }
};
