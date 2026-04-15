<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\DetailPembelianObat;
use App\Models\DetailResepObat;
use App\Models\Layanan;
use App\Models\Obat;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    /**
     * Helper: Mendapatkan rentang tanggal dari request
     */
    private function getFilterDates(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $parsedStartDate = Carbon::parse($startDate)->startOfMonth();
        $parsedEndDate = Carbon::parse($endDate)->endOfMonth();

        return [$parsedStartDate, $parsedEndDate];
    }

    /**
     * Helper: Menghitung Insight (Persentase & Tren)
     */
    private function calculateInsight(int $currentTotal, int $pastTotal): array
    {
        // 1. Handle jika data masa lalu kosong (division by zero prevention)
        if ($pastTotal == 0) {
            return [
                'percentage' => $currentTotal > 0 ? 100 : 0,
                'trend' => 'up',
                'text' => $currentTotal > 0 ? 'Meningkat 100% (Data sebelumnya kosong)' : 'Tidak ada perubahan',
                'diff_nominal' => $currentTotal
            ];
        }

        // 2. Hitung selisih dan persentase
        $diff = $currentTotal - $pastTotal;
        $percentage = round(($diff / $pastTotal) * 100, 1);

        // 3. Tentukan arah tren dan teks
        if ($diff > 0) {
            $trend = 'up';
            $text = "Naik " . abs($percentage) . "% dari periode sebelumnya";
        } elseif ($diff < 0) {
            $trend = 'down';
            $text = "Turun " . abs($percentage) . "% dari periode sebelumnya";
        } else {
            $trend = 'neutral';
            $text = "Stabil (Sama dengan periode sebelumnya)";
        }

        return [
            'percentage' => abs($percentage),
            'trend' => $trend,
            'text' => $text,
            'diff_nominal' => $diff
        ];
    }

    /**
     * 1. Kunjungan Pasien (Filter + Insight + Cache)
     */
    public function kunjunganPasien(Request $request)
    {
        [$startCarbon, $endCarbon] = $this->getFilterDates($request);

        $start = $startCarbon->copy()->startOfDay();
        $end = $endCarbon->copy()->endOfDay();

        $grouping = $request->query('periode', 'harian');

        // =====================
        // FORMAT GROUPING
        // =====================
        switch ($grouping) {
            case 'mingguan':
                $groupExpr = "YEARWEEK(created_at, 1)";
                $labelExpr = "CONCAT('Minggu ', WEEK(created_at, 1), ' ', YEAR(created_at))";
                break;

            case 'bulanan':
                $groupExpr = "DATE_FORMAT(created_at, '%Y-%m')";
                $labelExpr = "DATE_FORMAT(created_at, '%Y-%m')";
                break;

            case 'tahunan':
                $groupExpr = "YEAR(created_at)";
                $labelExpr = "YEAR(created_at)";
                break;

            default:
                $groupExpr = "DATE(created_at)";
                $labelExpr = "DATE(created_at)";
                $grouping = 'harian';
        }

        // =====================
        // A. BASE QUERY (REUSE)
        // =====================
        $baseQuery = RekamMedis::query()
            ->whereBetween('created_at', [$start, $end]);

        // =====================
        // B. TIME SERIES (AGREGASI DB)
        // =====================
        $filtered = $baseQuery
            ->select('created_at');

        $timeSeries = DB::query()
            ->fromSub($filtered, 'rm')
            ->selectRaw("
        {$groupExpr} AS period,
        {$labelExpr} AS date,
        COUNT(*) AS total
    ")
            ->groupBy('period', 'date')
            ->orderBy('period')
            ->get()
            ->map(fn($r) => [
                'date' => $r->date,
                'total' => (int) $r->total
            ])
            ->toArray();

        // =====================
        // C. TOTAL PERIODE INI (LANGSUNG DB)
        // =====================
        $currentTotal = (clone $baseQuery)->count();

        // =====================
        // D. PERIODE SEBELUMNYA (COUNT SAJA)
        // =====================
        $durationDays = $startCarbon->diffInDays($endCarbon) + 1;

        $prevStart = $startCarbon->copy()->subDays($durationDays)->startOfDay();
        $prevEnd = $endCarbon->copy()->subDays($durationDays)->endOfDay();

        $pastTotal = RekamMedis::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        // =====================
        // E. INSIGHT UTAMA
        // =====================
        $insightData = $this->calculateInsight($currentTotal, $pastTotal);

        // =====================
        // F. BUSIEST PERIOD (DB, BUKAN PHP SORT)
        // =====================
        $busiest = DB::query()
            ->fromSub($filtered, 'rm')
            ->selectRaw("
        {$labelExpr} AS label,
        COUNT(*) AS total
    ")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(1)
            ->first();


        $insightData['busiest'] = $busiest
            ? [
                'label' => $busiest->label,
                'total' => (int) $busiest->total,
                'grouping' => $grouping,
                'text' => ucfirst($grouping)
                    . " tersibuk terjadi pada {$busiest->label} dengan {$busiest->total} kunjungan"
            ]
            : null;

        // =====================
        // G. PER WILAYAH (TOP 5)
        // =====================
        $perWilayah = DB::query()
            ->fromSub(
                RekamMedis::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->select('no_pasien'),
                'rm'
            )
            ->join('pasiens', 'rm.no_pasien', '=', 'pasiens.no_pasien')
            ->join('tbl_regions', 'pasiens.kode_kecamatan', '=', 'tbl_regions.region_code')
            ->selectRaw('tbl_regions.region_name AS wilayah, COUNT(*) AS total')
            ->groupBy('tbl_regions.region_code', 'tbl_regions.region_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();

        return CommonResponse::ok([
            'label' => "Data Periode {$startCarbon->toDateString()} s/d {$endCarbon->toDateString()}",
            'summary' => [
                'total_kunjungan' => $currentTotal,
                'insight' => $insightData,
            ],
            'time_series' => $timeSeries,
            'per_wilayah' => $perWilayah,
        ]);
    }


    /**
     * 2. Waktu Tunggu Rata-rata (Bar Chart per Layanan)
     */
    public function waktuTunggu(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Durasi periode (detik)
        $durationInSeconds = $start->diffInSeconds($end);

        // Periode sebelumnya
        $prevStart = $start->copy()->subSeconds($durationInSeconds);
        $prevEnd = $start;

        // =====================
        // DATA PER LAYANAN
        // =====================
        $perLayanan = RekamMedis::query()
            ->join('layanans', 'rekam_medis.kode_layanan', '=', 'layanans.id')
            ->whereNotNull('rekam_medis.waktu_dilayani')
            ->whereBetween('rekam_medis.created_at', [$start, $end])
            ->select([
                'layanans.nama_layanan as layanan',
                DB::raw('AVG(TIMESTAMPDIFF(
                MINUTE,
                rekam_medis.created_at,
                rekam_medis.waktu_dilayani
            )) as avg_minutes')
            ])
            ->groupBy('layanans.nama_layanan')
            ->get()
            ->map(function ($item) {
                return [
                    'layanan' => $item->layanan,
                    'avg_minutes' => (int) round($item->avg_minutes),
                    'target_threshold' => 15,
                ];
            })
            ->values();

        // =====================
        // INSIGHT: LAYANAN TERLAMA
        // =====================
        $layananTerlama = $perLayanan->sortByDesc('avg_minutes')->first();

        $insightTerlama = $layananTerlama
            ? [
                'layanan' => $layananTerlama['layanan'],
                'avg_minutes' => $layananTerlama['avg_minutes'],
                'pesan' => "Waktu tunggu terlama terjadi pada {$layananTerlama['layanan']} dengan rata-rata {$layananTerlama['avg_minutes']} menit",
            ]
            : null;

        // =====================
        // RATA-RATA PERIODE AKTIF
        // =====================
        $currentAvg = RekamMedis::query()
            ->whereNotNull('waktu_dilayani')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(
            MINUTE,
            created_at,
            waktu_dilayani
        )) as avg'))
            ->value('avg');

        // =====================
        // RATA-RATA PERIODE SEBELUMNYA
        // =====================
        $previousAvg = RekamMedis::query()
            ->whereNotNull('waktu_dilayani')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(
            MINUTE,
            created_at,
            waktu_dilayani
        )) as avg'))
            ->value('avg');

        $currentAvg = $currentAvg ? round($currentAvg) : 0;
        $previousAvg = $previousAvg ? round($previousAvg) : 0;

        // =====================
        // RINGKASAN
        // =====================
        $diffMinutes = $currentAvg - $previousAvg;

        if ($diffMinutes > 0) {
            $keterangan = "Bertambah {$diffMinutes} menit dari periode terakhir";
        } elseif ($diffMinutes < 0) {
            $keterangan = 'Berkurang ' . abs($diffMinutes) . ' menit dari periode terakhir';
        } else {
            $keterangan = 'Tidak ada perubahan dibanding periode terakhir';
        }

        $data = [
            'per_layanan' => $perLayanan,
            'ringkasan' => [
                'rata_rata' => $currentAvg,
                'perbedaan' => $diffMinutes,
                'keterangan' => $keterangan,
                'layanan_terlama' => $insightTerlama,
            ],
        ];

        return CommonResponse::ok($data);
    }

    /**
     * 3. Jenis dan Tren Penyakit (Top 10 & Grouped Bar Chart)
     */
    public function jenisTrenPenyakit(Request $request)
    {
        [$startCarbon, $endCarbon] = $this->getFilterDates($request);

        $start = $startCarbon->copy()->startOfDay();
        $end = $endCarbon->copy()->endOfDay();

        $grouping = $request->query('periode', 'harian');

        // =====================
        // FORMAT GROUPING
        // =====================
        switch ($grouping) {
            case 'mingguan':
                $groupBy = "YEARWEEK(rekam_medis.created_at, 1)";
                break;

            case 'bulanan':
                $groupBy = "DATE_FORMAT(rekam_medis.created_at, '%Y-%m')";
                break;

            case 'tahunan':
                $groupBy = "YEAR(rekam_medis.created_at)";
                break;

            default:
                $groupBy = "DATE(rekam_medis.created_at)";
                $grouping = 'harian';
        }

        // =====================
        // A. TOP 10 PENYAKIT
        // =====================
        $top10Penyakit = RekamMedis::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('diagnosa_akhir')
            ->selectRaw('diagnosa_akhir AS nama, COUNT(*) AS jumlah')
            ->groupBy('diagnosa_akhir')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'nama' => $row->nama,
                'jumlah' => (int) $row->jumlah,
            ])
            ->toArray();

        // =====================
        // B. TOP 1 PENYAKIT DI TOP 5 WILAYAH
        // =====================

        // 1️⃣ Cari TOP 5 wilayah dengan kunjungan tertinggi
        $topWilayah = RekamMedis::query()
            ->join('pasiens', 'rekam_medis.no_pasien', '=', 'pasiens.no_pasien')
            ->join('tbl_regions', 'pasiens.kode_kecamatan', '=', 'tbl_regions.region_code')
            ->whereBetween('rekam_medis.created_at', [$start, $end])
            ->selectRaw('tbl_regions.region_code, tbl_regions.region_name, COUNT(*) as total')
            ->groupBy('tbl_regions.region_code', 'tbl_regions.region_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topWilayahPenyakit = [];

        // 2️⃣ Untuk tiap wilayah, ambil penyakit paling dominan
        $topWilayahPenyakit = [];

        foreach ($topWilayah as $wilayah) {
            $penyakits = RekamMedis::query()
                ->join('pasiens', 'rekam_medis.no_pasien', '=', 'pasiens.no_pasien')
                ->where('pasiens.kode_kecamatan', $wilayah->region_code)
                ->whereBetween('rekam_medis.created_at', [$start, $end])
                ->whereNotNull('diagnosa_akhir')
                ->selectRaw('diagnosa_akhir, COUNT(*) AS jumlah')
                ->groupBy('diagnosa_akhir')
                ->orderByDesc('jumlah')
                ->limit(3)
                ->get();

            foreach ($penyakits as $penyakit) {
                $topWilayahPenyakit[] = [
                    'wilayah' => $wilayah->region_name,
                    'penyakit' => $penyakit->diagnosa_akhir,
                    'jumlah' => (int) $penyakit->jumlah,
                ];
            }
        }

        // =====================
        // RESPONSE
        // =====================
        return CommonResponse::ok([
            'top_10_penyakit' => $top10Penyakit,
            'top_wilayah_penyakit' => $topWilayahPenyakit,
        ]);
    }

    /**
     * 4. Pendapatan dan Pengeluaran (Line Charts)
     */
    public function pendapatanPengeluaran(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $periode = $request->query('periode', 'bulanan');

        // 2. Setup Grouping (TAMBAH LOGIC TAHUNAN)
        if ($periode == 'harian') {
            // Key: "2025-01-15"
            $groupBy = "pembelian_obats.tanggal";
            $selectRaw = "pembelian_obats.tanggal as waktu";

            $groupByPendapatan = "DATE(rekam_medis.created_at)";
            $selectRawPendapatan = "DATE(rekam_medis.created_at) as waktu";

        } elseif ($periode == 'mingguan') {
            // Key: "202501" (Tahun 2025, Minggu 01)
            $groupBy = "YEARWEEK(pembelian_obats.tanggal)";
            $selectRaw = "YEARWEEK(pembelian_obats.tanggal) as waktu";

            $groupByPendapatan = "YEARWEEK(rekam_medis.created_at)";
            $selectRawPendapatan = "YEARWEEK(rekam_medis.created_at) as waktu";

        } elseif ($periode == 'tahunan') { // <--- FITUR BARU: TAHUNAN
            // Key: "2025"
            $groupBy = "YEAR(pembelian_obats.tanggal)";
            $selectRaw = "YEAR(pembelian_obats.tanggal) as waktu";

            $groupByPendapatan = "YEAR(rekam_medis.created_at)";
            $selectRawPendapatan = "YEAR(rekam_medis.created_at) as waktu";

        } else {
            // Default: Bulanan. Key: "2025-01"
            $groupBy = "DATE_FORMAT(pembelian_obats.tanggal, '%Y-%m')";
            $selectRaw = "DATE_FORMAT(pembelian_obats.tanggal, '%Y-%m') as waktu";

            $groupByPendapatan = "DATE_FORMAT(rekam_medis.created_at, '%Y-%m')";
            $selectRawPendapatan = "DATE_FORMAT(rekam_medis.created_at, '%Y-%m') as waktu";
        }

        // 3. Query Pengeluaran
        $queryPengeluaran = \App\Models\PembelianObat::join('detail_pembelian_obats', 'pembelian_obats.no_transaksi', '=', 'detail_pembelian_obats.kode_pembelian')
            ->selectRaw("$selectRaw, SUM(detail_pembelian_obats.total) as total_biaya")
            ->groupByRaw($groupBy);

        if ($startDate && $endDate) {
            $queryPengeluaran->whereBetween('pembelian_obats.tanggal', [$startDate, $endDate]);
        }
        $pengeluaran = $queryPengeluaran->pluck('total_biaya', 'waktu')->toArray();

        // 4. Query Pendapatan
        $queryPendapatan = RekamMedis::join('layanans', 'rekam_medis.kode_layanan', '=', 'layanans.id')
            ->selectRaw("$selectRawPendapatan, SUM(layanans.harga) as total")
            ->groupByRaw($groupByPendapatan);

        if ($startDate && $endDate) {
            $queryPendapatan->whereBetween('rekam_medis.created_at', [$startDate, $endDate]);
        }
        $pendapatan = $queryPendapatan->pluck('total', 'waktu')->toArray();

        // 5. Gabungkan & Sort
        $semuaWaktu = array_unique(array_merge(array_keys($pengeluaran), array_keys($pendapatan)));

        $semuaWaktu = array_filter($semuaWaktu, function ($value) {
            return !is_null($value) && $value !== '';
        });

        sort($semuaWaktu);

        $trenKeuangan = [];

        foreach ($semuaWaktu as $waktu) {
            $displayText = $waktu;

            // --- LOGIC PERCANTIK TAMPILAN ---
            if ($periode == 'mingguan') {
                $tahun = substr($waktu, 0, 4);
                $minggu = substr($waktu, 4);
                $displayText = "Minggu ke-" . intval($minggu) . " (" . $tahun . ")";
            } elseif ($periode == 'bulanan') {
                try {
                    $displayText = \Carbon\Carbon::createFromFormat('Y-m', $waktu)->format('M Y');
                } catch (\Exception $e) {
                    $displayText = $waktu;
                }
            } elseif ($periode == 'harian') {
                try {
                    $displayText = \Carbon\Carbon::parse($waktu)->format('d M Y');
                } catch (\Exception $e) {
                    $displayText = $waktu;
                }
            }
            // elseif ($periode == 'tahunan') { 
            //     // Tidak perlu diapa-apakan, karena isinya sudah "2025", "2026", dst.
            // }

            $trenKeuangan[] = [
                "bulan" => $displayText, // Key tetap "bulan" agar frontend aman
                "pendapatan" => (int) ($pendapatan[$waktu] ?? 0),
                "pengeluaran" => (int) ($pengeluaran[$waktu] ?? 0)
            ];
        }

        return CommonResponse::ok([
            'tren_keuangan' => $trenKeuangan
        ]);
    }

    /**
     * 5. Margin Keuntungan
     */
    public function marginKeuntungan(Request $request)
    {
        $filter = $request->query('periode', 'bulanan');
        $hasDateFilter = $request->has(['start_date', 'end_date']);

        // =========================
        // FORMAT GROUPING PERIODE
        // =========================
        switch ($filter) {
            case 'mingguan':
                $groupBy = "YEARWEEK(%s, 1)";
                break;

            case 'tahunan':
                $groupBy = "YEAR(%s)";
                break;

            default:
                $groupBy = "DATE_FORMAT(%s, '%Y-%m')";
                $filter = 'bulanan';
        }

        // =========================
        // FILTER TANGGAL (OPSIONAL)
        // =========================
        $start = null;
        $end = null;

        if ($hasDateFilter) {
            [$startCarbon, $endCarbon] = $this->getFilterDates($request);
            $start = $startCarbon->startOfDay();
            $end = $endCarbon->endOfDay();
        }

        $prevStart = null;
        $prevEnd = null;

        if ($hasDateFilter) {
            $durationInSeconds = $start->diffInSeconds($end);
            $prevStart = $start->copy()->subSeconds($durationInSeconds);
            $prevEnd = $end->copy()->subSeconds($durationInSeconds);
        }

        // =========================
        // PENGELUARAN (MODAL OBAT)
        // =========================
        $modalQuery = DB::table('detail_pembelian_obats')
            ->join(
                'pembelian_obats',
                'detail_pembelian_obats.kode_pembelian',
                '=',
                'pembelian_obats.no_transaksi'
            )
            ->selectRaw(
                str_replace('%s', 'pembelian_obats.tanggal', $groupBy) . ' AS periode,
            SUM(detail_pembelian_obats.total) AS total'
            );

        if ($hasDateFilter) {
            $modalQuery->whereBetween('pembelian_obats.tanggal', [$start, $end]);
        }

        $modalData = $modalQuery->groupBy('periode')->get();

        $totalModal = $modalData->sum('total');
        $jumlahPeriodeModal = $modalData->count();

        // =========================
        // PEMASUKAN OBAT
        // =========================
        $obatQuery = DB::table('detail_resep_obats')
            ->selectRaw(
                str_replace('%s', 'created_at', $groupBy) . ' AS periode,
            SUM(total) AS total'
            );

        if ($hasDateFilter) {
            $obatQuery->whereBetween('created_at', [$start, $end]);
        }

        $obatData = $obatQuery->groupBy('periode')->get();

        // =========================
        // PEMASUKAN LAYANAN
        // =========================
        $layananQuery = DB::table('rekam_medis')
            ->join(
                'layanans',
                'rekam_medis.kode_layanan',
                '=',
                'layanans.id'
            )
            ->selectRaw(
                str_replace('%s', 'rekam_medis.created_at', $groupBy) . ' AS periode,
            SUM(layanans.harga) AS total'
            );

        if ($hasDateFilter) {
            $layananQuery->whereBetween('rekam_medis.created_at', [$start, $end]);
        }

        $layananData = $layananQuery->groupBy('periode')->get();

        // =========================
        // TOTAL PEMASUKAN
        // =========================
        $totalPendapatan = $obatData->sum('total') + $layananData->sum('total');

        $jumlahPeriodePendapatan = max(
            $obatData->count(),
            $layananData->count()
        );

        // =========================
        // RATA-RATA
        // =========================
        $rataPendapatan = $jumlahPeriodePendapatan > 0
            ? $totalPendapatan / $jumlahPeriodePendapatan
            : 0;

        $rataPengeluaran = $jumlahPeriodeModal > 0
            ? $totalModal / $jumlahPeriodeModal
            : 0;

        // =========================
        // MARGIN
        // =========================
        $marginNominal = $totalPendapatan - $totalModal;
        $marginPersen = $totalPendapatan > 0
            ? round(($marginNominal / $totalPendapatan) * 100, 2)
            : 0;

        // =========================
        // LABEL
        // =========================
        $label = $marginNominal >= 0 ? 'Positif' : 'Negatif';

        $previousMargin = 0;

        if ($hasDateFilter) {
            $previousMargin = $this->hitungMargin(
                $groupBy,
                $prevStart,
                $prevEnd
            );
        }

        $lastDiff = $marginNominal - $previousMargin;
        $trendText = $lastDiff > 0
            ? "Margin meningkat sebesar Rp " . number_format($lastDiff)
            : ($lastDiff < 0
                ? "Margin menurun sebesar Rp " . number_format(abs($lastDiff))
                : "Margin tidak berubah dibanding periode sebelumnya");


        // =========================
        // RESPONSE (TIDAK BERUBAH)
        // =========================
        return CommonResponse::ok([
            'filter' => $filter,
            'total_pendapatan' => (int) $totalPendapatan,
            'total_pengeluaran' => (int) $totalModal,
            'margin_nominal' => (int) $marginNominal,
            'margin_percentage' => $marginPersen,
            'label' => $label,
            'rata_rata' => [
                'pendapatan' => (int) $rataPendapatan,
                'pengeluaran' => (int) $rataPengeluaran
            ],
            'insight' => [
                'perbedaan' => $lastDiff,
                'text' => $trendText
            ]
        ]);
    }

    private function hitungMargin($groupBy, $start = null, $end = null)
    {
        // MODAL
        $modal = DB::table('detail_pembelian_obats')
            ->join(
                'pembelian_obats',
                'detail_pembelian_obats.kode_pembelian',
                '=',
                'pembelian_obats.no_transaksi'
            )
            ->when(
                $start && $end,
                fn($q) =>
                $q->whereBetween('pembelian_obats.tanggal', [$start, $end])
            )
            ->sum('detail_pembelian_obats.total');

        // PEMASUKAN OBAT
        $pendapatanObat = DB::table('detail_resep_obats')
            ->when(
                $start && $end,
                fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->sum('total');

        // PEMASUKAN LAYANAN
        $pendapatanLayanan = DB::table('rekam_medis')
            ->join('layanans', 'rekam_medis.kode_layanan', '=', 'layanans.id')
            ->when(
                $start && $end,
                fn($q) =>
                $q->whereBetween('rekam_medis.created_at', [$start, $end])
            )
            ->sum('layanans.harga');

        $totalPendapatan = $pendapatanObat + $pendapatanLayanan;

        return $totalPendapatan - $modal;
    }

    /**
     * 6. Inventory Turnover Rate (Bar Chart per Kategori Obat)
     */
    public function ketersediaanObat(Request $request)
    {
        $categories = Obat::query()
            ->join('tipe_obats', 'obats.kode_tipe', '=', 'tipe_obats.kode')
            ->selectRaw('
            tipe_obats.nama as kategori,
            SUM(obats.stok) as stok
        ')
            ->groupBy('tipe_obats.kode', 'tipe_obats.nama')
            ->orderByDesc('stok')
            ->get()
            ->map(fn($row) => [
                'kategori' => $row->kategori,
                'stok' => (int) $row->stok,
            ])
            ->toArray();

        return CommonResponse::ok([
            'categories' => $categories
        ]);
    }
}