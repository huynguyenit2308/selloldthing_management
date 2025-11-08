<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
        	$table->string('username')->nullable();
       	 	$table->string('fullname')->nullable();
       		$table->string('avatar')->nullable();
       	 	$table->string('provider')->nullable();
     	    $table->string('provider_id')->nullable();
            $table->boolean('is_new')->default(true)->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_new');
        });
    }
};
