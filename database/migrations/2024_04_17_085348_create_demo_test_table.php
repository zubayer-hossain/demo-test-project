<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoTestTable extends Migration
{
    public function up()
    {
        Schema::create('demo_test', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['NEW', 'UPDATED']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('demo_test');
    }
}
