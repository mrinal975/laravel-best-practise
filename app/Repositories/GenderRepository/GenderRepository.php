<?php

namespace App\Repositories;

use App\Models\Gender\Gender;

class GenderRepository extends BaseRepository
{
    /**
     * Create a new repository instance.
     *
     * @param  Gender  $gender
     * @return void
     */
    public function __construct(Gender $gender)
    {
        $this->model = $gender;
    }
}
