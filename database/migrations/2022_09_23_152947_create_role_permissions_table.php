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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('role_id')->unsigned()->nullable()->default(1);
            $table->bigInteger('page_id')->unsigned()->default(1);
            $table->boolean('is_checked')->default(false);
            $table->string('permission')->default('full access');
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
        Schema::dropIfExists('role_permissions');
    }
};
