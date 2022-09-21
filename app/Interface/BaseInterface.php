<?php

namespace App\Interface;

use Illuminate\Http\Request;

interface BaseInterface{


    public function index(Request $request);

    public function store(Request $request);

    public function create(Request $request);

    public function show(Request $request, $id);
    
    public function update(Request $request, $id);

    public function destroy(Request $request, $id);

}