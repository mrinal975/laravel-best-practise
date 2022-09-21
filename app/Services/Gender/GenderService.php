<?php

namespace App\Services\Auth;

use App\Repositories\GenderRepository;

class GenderService
{

    public function __construct(private GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

}