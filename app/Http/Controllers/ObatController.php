<?php

namespace App\Http\Controllers;

use App\Http\Responses\CommonResponse;
use App\Models\Obat;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('read obat')) {
            return CommonResponse::forbidden();
        }
        $obats = Obat::with(['satuanObat', 'tipeObat'])->orderBy('nama_obat', 'asc')->get();

        return CommonResponse::ok($obats->toArray());
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
            'nama_obat' => 'required|string',
            'kode_tipe' => 'required|exists:tipe_obats,kode',
            'satuan' => 'required|exists:satuan_obats,kode',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'stok' => 'required|integer',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return CommonResponse::badRequest($validator->errors()->toArray());
        }

        $obat = Obat::create($request->input());
        $obat = Obat::with('satuanObat', 'tipeObat')->find($obat->kode);

        return CommonResponse::created($obat->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        if (!$request->user()->can('view obat')) {
            return CommonResponse::forbidden();
        }

        $obat = Obat::with(['satuanObat', 'tipeObat'])->find($id);
        if ($obat == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($obat->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user()->can('update obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->input(), [
            'nama_obat' => 'string',
            'kode_tipe' => 'exists:tipe_obats,kode',
            'satuan' => 'exists:satuan_obats,kode',
            'harga_beli' => 'numeric',
            'harga_jual' => 'numeric',
            'stok' => 'integer',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return CommonResponse::badRequest($validator->errors()->toArray());
        }

        $obat = Obat::find($id);
        if ($obat == null) {
            return CommonResponse::notFound();
        }

        $obat->update($request->input());
        $obat = Obat::with('satuanObat', 'tipeObat')->find($id);

        return CommonResponse::ok($obat->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->can('delete obat')) {
            return CommonResponse::forbidden();
        }

        $obat = Obat::with('satuanObat', 'tipeObat')->find($id);
        if ($obat == null) {
            return CommonResponse::notFound();
        }
        $obat->delete();

        return CommonResponse::ok($obat->toArray());
    }
}
