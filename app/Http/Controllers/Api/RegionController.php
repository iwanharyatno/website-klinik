<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index($parent) {
        if ($parent == null) $parent = 0;

        $response = Region::where('parent_code', $parent)->get();

        return CommonResponse::ok($response);
    }
}
