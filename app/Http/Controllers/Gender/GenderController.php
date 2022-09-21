<?php

namespace App\Http\Controllers\Gender;

use App\Http\Controllers\Controller;
use App\Services\Gender\GenderService;
use App\Http\Requests\Gender\GenderRequest;
use Illuminate\Http\Request;
class GenderController extends Controller
{
    public function __construct(private GenderService $genderService)
    {
        $this->genderService = $genderService;
    }

    public function index(Request $request){
        return $this->genderService->index($request);
    }

    public function create(Request $request)
    {
        return $this->genderService->create($request);
    }

    public function store(GenderRequest $request)
    {
        return $this->genderService->store($request);
    }

    public function show(Request $request, $id)
    {
        return $this->genderService->show($request, $id);
    }

    public function update(GenderRequest $request, $id)
    {
        return $this->genderService->update($request, $id);
    }

    public function destroy(Request $request, $id)
    {
        return $this->genderService->destroy($request, $id);
    }
    
}
