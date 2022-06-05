<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->longtext('prof_img_url')->default('RrmDmqreoLbR6dhjSVuFenDAii8uBWdqhi2fYSjK9pRISPykLSdefaultprofileimg.jpg');
            $table->longText('facebook_url')->nullable();
            $table->longText('whatsapp_url')->nullable();
            $table->string('phone_number')->nullable();

            $table->foreignId('role_id')->default(1)->constrained('roles')->cascadeOnDelete();

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
