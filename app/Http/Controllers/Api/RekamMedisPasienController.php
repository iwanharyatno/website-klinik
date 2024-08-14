<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RekamMedisPasienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $pasienId)
    {
        if (!$request->user()->can('read rekam_medis')) {
            return CommonResponse::forbidden();
        }
        $pasien = Pasien::find($pasienId);
        $rekamMedisList = $pasien->rekamMedis()->orderBy('created_at', 'desc')->get();

        return CommonResponse::ok($rekamMedisList->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $pasienId)
    {
        if (!$request->user()->can('create rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $pasien = Pasien::find($pasienId);
        $rekamMedis = $pasien->rekamMedis()->create($request->all());

        return CommonResponse::created($rekamMedis->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $pasienId, string $id)
    {
        if (!$request->user()->can('read rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $pasien = Pasien::find($pasienId);
        $rekamMedis = $pasien->rekamMedis()->where('kode', $id)->first();

        if ($rekamMedis == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($rekamMedis->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $pasienId, string $id)
    {
        if (!$request->user()->can('update rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $pasien = Pasien::find($pasienId);
        $pasien->rekamMedis()->where('kode', $id)->update($request->all());

        $rekamMedis = $pasien->rekamMedis()->where('kode', $id)->first();

        return CommonResponse::ok($rekamMedis->toArray());
    }

    /** 
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $pasienId, string $id)
    {
        if (!$request->user()->can('delete rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $rekamMedis = RekamMedis::find($id);
        $rekamMedis->delete();

        return CommonResponse::ok($rekamMedis->toArray());
    }
}
