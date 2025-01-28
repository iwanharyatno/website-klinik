<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\Penyakit;
use Illuminate\Http\Request;

class PenyakitController extends Controller
{
    public function index() {
        $penyakit = Penyakit::orderBy('nama', 'ASC')->get();

        return CommonResponse::ok($penyakit);
    }
}
