<?php

namespace App\Repositories\GenderRepository;

use App\Models\Gender\Gender;
use App\Repositories\BaseRepository;

class GenderRepository extends BaseRepository
{
    /**
     * Create a new repository instance.
     *
     * @param  Gender  $gender
     * @return void
     */
    public function __construct(Gender $entityInstance)
    {
        $this->entityInstance = $entityInstance;
    }
}
