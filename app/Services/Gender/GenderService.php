<?php

namespace App\Services\Gender;

use App\Repositories\GenderRepository\GenderRepository;

class GenderService
{

    public function __construct(private GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

}