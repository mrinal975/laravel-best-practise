<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Engine\DbFields\Fields;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('translate')->nullable(); // description
            $table->string('link')->nullable();
            $table->bigInteger('parent_id')->unsigned()->default(0);
            $table->integer('order')->default(0);
            $table->integer('level')->nullable();
            $table->string('type')->nullable();
            $table->string('icon')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(1);
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
        Schema::dropIfExists('pages');
    }
};
