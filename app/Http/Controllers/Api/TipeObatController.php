<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\TipeObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipeObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipeObat = TipeObat::all();

        return CommonResponse::ok($tipeObat->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('create obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->input(), [
            'nama' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return CommonResponse::badRequest($validator->errors()->toArray());
        }

        $tipeObat = TipeObat::create($request->input());

        return CommonResponse::created($tipeObat->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tipeObat = TipeObat::find($id);

        if ($tipeObat == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($tipeObat->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('update obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->input(), [
            'nama' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return CommonResponse::badRequest($validator->errors()->toArray());
        }
        $tipeObat = TipeObat::find($id);

        if ($tipeObat == null) {
            return CommonResponse::notFound();
        }

        $tipeObat->update($request->input());

        return CommonResponse::ok($tipeObat->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->can('delete obat')) {
            return CommonResponse::forbidden();
        }
        $tipeObat = TipeObat::find($id);

        if ($tipeObat == null) {
            return CommonResponse::notFound();
        }
        $tipeObat->delete();

        return CommonResponse::ok($tipeObat->toArray());
    }
}
