<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\SuratKeteranganDokter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SKDController extends Controller
{
    public function saveLetter(Request $request) {
        if (!$request->user()->can('update rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $validator = Validator::make($request->all(), [
            'no_surat' => 'numeric|required',
            'no_bulan' => 'required',
            'no_tahun' => 'numeric|required',
            'no_surat_full' => 'required',
            'nama_pasien' => 'required',
            'umur' => 'numeric|required',
            'alamat' => 'required',
            'pekerjaan' => 'required',
            'mulai_istirahat' => 'required',
            'selesai_istirahat' => 'required'
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        $data = $validator->validated();

        $skd = SuratKeteranganDokter::create($data);

        return CommonResponse::ok($skd->toArray());
    }

    public function show(Request $request) {
        if (!$request->user()->can('update rekam_medis')) {
            return CommonResponse::forbidden();
        }

        $noSurat = $request->query('no_surat_full');
        $skd = SuratKeteranganDokter::where('no_surat_full', $noSurat)->first();
        if ($skd == null) {
            return CommonResponse::notFound();
        }

        return CommonResponse::ok($skd->toArray());
    }

    private function toRoman($month) {
        if ($month == "01") {
            return "I";
        }
        if ($month == "02") {
            return "II";
        }
        if ($month == "03") {
            return "III";
        }
        if ($month == "04") {
            return "IV";
        }
        if ($month == "05") {
            return "V";
        }
        if ($month == "06") {
            return "VI";
        }
        if ($month == "07") {
            return "VII";
        }
        if ($month == "08") {
            return "VIII";
        }
        if ($month == "09") {
            return "IX";
        }
        if ($month == "10") {
            return "X";
        }
        if ($month == "11") {
            return "XI";
        }
        if ($month == "12") {
            return "XII";
        }
        return "";
    }

    public function getCurrentNumbers(Request $request) {
        if (!$request->user()->can('update rekam_medis')) {
            return CommonResponse::forbidden();
        }
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->format('m');

        $maxNoSurat = SuratKeteranganDokter::whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->max('no_surat');

        return CommonResponse::ok([
            'no_tahun' => $currentYear,
            'no_bulan' => $this->toRoman($currentMonth),
            'no_surat' => $maxNoSurat + 1
        ]);
    }
}
