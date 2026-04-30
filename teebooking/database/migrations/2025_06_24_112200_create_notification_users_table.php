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
    Schema::create('notification_users', function (Blueprint $table) {
        $table->id();

        $table->foreignId('notification_id')->constrained()->onDelete('cascade');

        // Fix: manually define the user_id foreign key to memberprofile
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')->references('id')->on('memberprofile')->onDelete('cascade');

        $table->timestamp('sent_at')->nullable();
        $table->boolean('success')->default(false);

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
        Schema::dropIfExists('notification_users');
    }
};
