<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\Pasien;
use App\Models\SuratKeteranganDokter;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('read pasien')) {
            return CommonResponse::forbidden();
        }

        $pasienList = Pasien::orderBy('nama_pasien')->get();

        return CommonResponse::ok($pasienList->toArray());
    }

    /**
     * Display a listing of the resource with specific search modes.
     */
    public function indexSearch(Request $request)
    {
        // 1. Authorization check
        if (!$request->user()->can('read pasien')) {
            return CommonResponse::forbidden();
        }

        $mode = $request->query('mode'); // 'no_pasien', 'nama', or 'alamat'
        $keyword = $request->query('keyword');

        // Start with a clean query and specific selection
        $query = Pasien::query()->select('pasiens.*');

        if ($keyword) {
            $query->where(function ($q) use ($mode, $keyword) {
                switch ($mode) {
                    case 'no_pasien':
                        // Direct/Exact match for ID
                        $q->where('no_pasien', $keyword);
                        break;

                    case 'nama':
                        // Partial match for Name
                        $q->where('nama_pasien', 'like', "%{$keyword}%");
                        break;

                    case 'alamat':
                        // Complex Alamat Logic
                        $this->applyAlamatSearch($q, $keyword);
                        break;

                    default:
                        // Fallback: search all if no mode is specified
                        $q->where('no_pasien', 'like', "%{$keyword}%")
                            ->orWhere('nama_pasien', 'like', "%{$keyword}%");
                        $this->applyAlamatSearch($q, $keyword, true);
                        break;
                }
            });
        }

        // 2. Optimization: Eager load if needed, but for 12k+ keep it lean
        // 3. Mandatory Pagination
        $pasienList = $query->orderBy('nama_pasien')
            ->paginate($request->query('per_page', 15));

        return CommonResponse::ok($pasienList);
    }

    /**
     * Helper function to handle the complex address search logic
     */
    private function applyAlamatSearch($query, $keyword, $isOr = false)
    {
        $method = $isOr ? 'orWhere' : 'where';

        $query->$method(function ($q) use ($keyword) {
            // Search local text fields
            $q->where('alamat', 'like', "%{$keyword}%")
                ->orWhere('propinsi', 'like', "%{$keyword}%")
                ->orWhere('kabupaten', 'like', "%{$keyword}%")
                ->orWhere('kecamatan', 'like', "%{$keyword}%")
                ->orWhere('kelurahan', 'like', "%{$keyword}%");

            // Lookup Region Codes in tbl_regions
            $matchingRegionCodes = DB::table('tbl_regions')
                ->where('region_name', 'like', "%{$keyword}%")
                ->pluck('region_code');

            if ($matchingRegionCodes->isNotEmpty()) {
                $q->orWhereIn('kode_propinsi', $matchingRegionCodes)
                    ->orWhereIn('kode_kabupaten', $matchingRegionCodes)
                    ->orWhereIn('kode_kecamatan', $matchingRegionCodes)
                    ->orWhereIn('kode_kelurahan', $matchingRegionCodes);
            }
        });
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
            return CommonResponse::badRequest($validator->errors()->all());
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