<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('read pasien')) {
            return CommonResponse::forbidden();
        }

        $pasienList = Pasien::orderBy('nama_pasien')->get();

        return CommonResponse::ok($pasienList->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('create pasien')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'nama_pasien' => 'string|required',
            'jenis_kelamin' => 'in:Laki-laki,Perempuan',
            'status' => 'in:Sakit,Sembuh'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        $pasien = Pasien::create($request->all());

        return CommonResponse::created($pasien->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        if (!$request->user()->can('read pasien')) {
            return CommonResponse::forbidden();
        }

        $pasien = Pasien::find($id);

        if ($pasien == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($pasien->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user()->can('update pasien')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'nama_pasien' => 'string|required',
            'jenis_kelamin' => 'in:Laki-laki,Perempuan',
            'status' => 'in:Sakit,Sembuh'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        Pasien::where('no_pasien', $id)->update($request->all());

        $pasien = Pasien::find($id);

        return CommonResponse::ok($pasien->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->can('delete pasien')) {
            return CommonResponse::forbidden();
        }

        $pasien = Pasien::find($id);
        $pasien->delete();

        return CommonResponse::ok($pasien->toArray());
    }
}
