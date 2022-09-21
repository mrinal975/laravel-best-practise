<?php

namespace App\Http\Controllers\Gender;

use App\Http\Controllers\Controller;
use App\Services\Auth\GenderService;

class GenderController extends Controller
{
    public function __construct(private GenderService $genderService)
    {
        $this->genderService = $genderService;
    }
}
