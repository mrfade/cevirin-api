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
            $table->uuid('id');
            $table->primary('id');

            $table->foreignUuid('video_id')->constrained();

            $table->text('url');
            $table->string('ext');

            $table->longText('headers')->nullable();
            $table->ipAddress('ip');

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
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
