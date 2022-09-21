<?php

namespace App\Services\Gender;

use App\Repositories\GenderRepository\GenderRepository;
use Illuminate\Http\Request;
class GenderService
{

    public function __construct(private GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

    public function index(Request $request){
        return $this->genderRepository->index($request);
    }

    public function create(Request $request)
    {
        return $this->genderRepository->create($request);
    }

    public function store(Request $request)
    {
        return $this->genderRepository->store($request);
    }

    public function show(Request $request, $id)
    {
        return $this->genderRepository->show($request, $id);
    }

    public function update(Request $request, $id)
    {
        return $this->genderRepository->update($request, $id);
    }

    public function destroy(Request $request, $id)
    {
        return $this->genderRepository->destroy($request, $id);
    }

}