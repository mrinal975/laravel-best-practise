<?php

namespace App\Engine\DbFields;

use Illuminate\Support\Facades\Auth;

class Fields
{
    protected static $createdFields = ['created_by', 'updated_by'];
    protected static $updatedFields = ['updated_by'];

    public static function MakeCommonField($table)
    {
        $table->bigInteger('created_by')->nullable();
        $table->bigInteger('updated_by')->nullable();
        $table->bigInteger('deleted_by')->nullable();
        $table->bigInteger('company_id')->nullable();
        $table->softDeletes('deleted_at')->nullable();
        $table->timestamps();
        $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
    }

    public static function AddCommonField($table)
    {
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->softDeletes('deleted_at')->nullable();
        $table->timestamps();
    }

    public static function AddCommonFieldWithoutforeign($table)
    {
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->softDeletes('deleted_at')->nullable();
        $table->timestamps();
    }

    public static function createCommonFields($fields, $resource): array
    {
        // $user_id = Auth::user()->id ;
        // $resource->created_by = $user_id;
        // $resource->updated_by = $user_id;
        // $resource->company_id = Auth::user()->company_id;
        return [
            'fields' => $fields,
            'resource' => $resource
        ];
    }

    public static function updatedCommonFields($fields, $resource): array
    {
        $user_id = Auth::user()->id;
        $resource->updated_by = $user_id;
        return [
            'fields' => $fields,
            'resource' => $resource
        ];
    }

    public static function RenameCommon($table)
    {
        $table->renameColumn('isActive', 'is_active');
        $table->renameColumn('isDefault', 'is_default');
        // $table->renameColumn('softDelete', 'soft_delete');
    }

    public static function RenameField($table)
    {
        $table->renameColumn('isActive', 'is_active');
        $table->renameColumn('isDefault', 'is_default');
    }
}
