<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.index');
    }

    public function antrian()
    {
        $next = RekamMedis::whereDate('created_at', Carbon::today())->where('status', 'Menunggu')->min('no_antrian');
        $current = RekamMedis::whereDate('created_at', Carbon::today())->where('status', 'Selesai')->max('no_antrian');

        if ($next == null) {
            $next = "-";
        }
        if ($current == null) {
            $current = "-";
        }

        return view('home.antrian', compact('next', 'current'));
    }
}
