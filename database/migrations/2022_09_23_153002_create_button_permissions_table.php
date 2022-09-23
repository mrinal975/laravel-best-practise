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
        Schema::create('button_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_id');
            $table->string('page_id'); // description
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('organization_id')->nullable();
            Fields::AddCommonFieldWithoutforeign($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('button_permissions');
    }
};
