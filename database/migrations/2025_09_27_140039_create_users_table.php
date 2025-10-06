<?php
// database/migrations/2024_01_01_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('nip')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('bidang_id');
            $table->enum('role', ['admin', 'staf']);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('bidang_id')->references('id')->on('bidangs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};