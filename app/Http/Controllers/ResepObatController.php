<?php

namespace App\Http\Controllers;

use App\Http\Responses\CommonResponse;
use App\Models\DetailResepObat;
use App\Models\Obat;
use App\Models\RekamMedis;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResepObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $recordId)
    {
        if (!$request->user()->can('read resep_obat')) {
            return CommonResponse::forbidden();
        }
        $pasien = RekamMedis::find($recordId);
        $resepObatList = $pasien->resepObat()->with(['rekamMedis', 'detailResepObats', 'detailResepObats.obat', 'detailResepObats.obat.satuanObat', 'detailResepObats.obat.tipeObat'])->orderBy('created_at', 'desc')->first();

        if ($resepObatList == null) {
            return CommonResponse::ok((object)[]);
        }

        return CommonResponse::ok($resepObatList->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $recordId)
    {
        if (!$request->user()->can('create resep_obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'kode_obat' => 'required',
            'harga' => 'required',
            'kuantitas' => 'required',
            'total' => 'required'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        $data = $validator->validated();

        $pasien = RekamMedis::find($recordId);
        $resepObat = $pasien->resepObat()->first();

        if ($resepObat == null) {
            $resepObat = $pasien->resepObat()->create([
                'total' => $data['total'],
            ]);
        }

        $obat = Obat::find($data['kode_obat']);
        $obat->stok -= $data['kuantitas'];
        $obat->save();
        
        $detailResep = $resepObat->detailResepObats()->create($request->all());

        return CommonResponse::created($detailResep->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $recordId, string $id)
    {
        if (!$request->user()->can('read resep_obat')) {
            return CommonResponse::forbidden();
        }

        $pasien = RekamMedis::find($recordId);
        $resepObat = $pasien->resepObat()->first();

        if ($resepObat == null) {
            return CommonResponse::notFound();
        }

        $detailResep = $resepObat->detailResepObats()->with(['obat', 'obat.satuanObat', 'obat.tipeObat'])->where('kode', $id)->orderBy('created_at', 'desc')->first();

        if ($detailResep == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($detailResep->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $recordId, string $id)
    {
        if (!$request->user()->can('update resep_obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'kode_obat' => 'required',
            'harga' => 'required',
            'kuantitas' => 'required',
            'total' => 'required'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        $rekamMedis = RekamMedis::find($recordId);
        $resepObat = $rekamMedis->resepObat()->first();

        $detailResep = $resepObat->detailResepObats()->where('kode', $id)->update($request->all());
        $detailResep = $resepObat->detailResepObats()->where('kode', $id)->first();

        return CommonResponse::ok($detailResep->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $recordId, string $id)
    {
        if (!$request->user()->can('delete resep_obat')) {
            return CommonResponse::forbidden();
        }

        $resepObat = DetailResepObat::find($id);
        $resepObat->delete();

        return CommonResponse::ok($resepObat->toArray());
    }

    public function pay(Request $request, $recordId)
    {
        if (!$request->user()->can('update resep_obat')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'jml_bayar' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        if (!$request->user()->can('update resep_obat')) {
            return CommonResponse::forbidden();
        }

        $data = $validator->validated();

        $resep = ResepObat::where('kode_rekam_medis', $recordId)->first();

        ResepObat::where('kode_rekam_medis', $recordId)->update([
            'status' => $resep->total <= $data['jml_bayar'],
            'jml_bayar' => $data['jml_bayar'],
            'total' => $request->total
        ]);

        $updated = ResepObat::where('kode_rekam_medis', $recordId)->first();

        return CommonResponse::ok($updated->toArray());
    }
}
