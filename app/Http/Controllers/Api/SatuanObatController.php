<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\SatuanObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SatuanObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('read obat')) {
            return CommonResponse::forbidden();
        }

        $satuanObats = SatuanObat::all();

        return CommonResponse::ok($satuanObats->toArray());
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

        $satuanObat = SatuanObat::create($request->input());

        return CommonResponse::created($satuanObat->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $satuanObat = SatuanObat::find($id);

        if ($satuanObat == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($satuanObat->toArray());
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

        $satuanObat = SatuanObat::find($id);

        if ($satuanObat == null) {
            return CommonResponse::notFound();
        }

        $satuanObat->update($request->input());

        return CommonResponse::ok($satuanObat->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->can('delete obat')) {
            return CommonResponse::forbidden();
        }

        $satuanObat = SatuanObat::find($id);

        if ($satuanObat == null) {
            return CommonResponse::notFound();
        }

        $satuanObat->delete();

        return CommonResponse::ok($satuanObat->toArray());
    }
}
