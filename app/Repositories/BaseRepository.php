<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    /**
     * Create a new repository instance.
     *
     * @param  Admin  $admin
     * @return void
     */
    public function __construct(Model $admin)
    {
        $this->model = $admin;
    }
}
