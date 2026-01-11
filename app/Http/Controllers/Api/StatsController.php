<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\RekamMedis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    /**
     * Helper untuk mendapatkan rentang tanggal filter.
     * Default: Awal bulan ini sampai akhir bulan ini.
     */
    private function getFilterDates(Request $request)
    {
        // Mengambil dari query param, jika tidak ada gunakan default awal & akhir bulan berjalan
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $parsedStartDate = Carbon::parse($startDate)->startOfMonth();
        $parsedEndDate = Carbon::parse($endDate)->endOfMonth();

        return [$parsedStartDate, $parsedEndDate];
    }

    /**
     * 1. Kunjungan Pasien (Line Chart & Bar Chart per Wilayah)
     */
    public function kunjunganPasien(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        // TODO: Gunakan ->whereBetween('tanggal_kolom', [$startDate, $endDate])
        $data = [
            'time_series' => [], // Eloquent: count kunjungan group by date
            'per_wilayah' => [], // Eloquent: count kunjungan group by wilayah
        ];

        return CommonResponse::ok($data, "Statistik kunjungan pasien periode $startDate s/d $endDate berhasil diambil");
    }

    /**
     * 2. Waktu Tunggu Rata-rata (Bar Chart per Layanan)
     */
    public function waktuTunggu(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Durasi periode (dalam detik)
        $durationInSeconds = $start->diffInSeconds($end);

        // Periode sebelumnya
        $prevStart = $start->copy()->subSeconds($durationInSeconds);
        $prevEnd = $start;

        // =====================
        // DATA PER LAYANAN
        // =====================
        $perLayanan = RekamMedis::query()
            ->select(['jenis_tindakan as layanan', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, waktu_dilayani)) as avg_minutes')])
            ->whereNotNull('jenis_tindakan')
            ->whereNotNull('waktu_dilayani')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('jenis_tindakan')
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
        // RATA-RATA PERIODE AKTIF
        // =====================
        $currentAvg = RekamMedis::query()
            ->whereNotNull('waktu_dilayani')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, waktu_dilayani)) as avg'))
            ->value('avg');

        // =====================
        // RATA-RATA PERIODE SEBELUMNYA
        // =====================
        $previousAvg = RekamMedis::query()
            ->whereNotNull('waktu_dilayani')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, waktu_dilayani)) as avg'))
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
            ],
        ];

        return CommonResponse::ok($data, 'Data waktu tunggu dimuat');
    }

    /**
     * 3. Jenis dan Tren Penyakit (Top 10 & Grouped Bar Chart)
     */
    public function jenisTrenPenyakit(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $data = [
            'top_10_penyakit' => [], // Eloquent: count penyakit limit 10
            'top_wilayah_penyakit' => [], // Eloquent: group by wilayah & penyakit
        ];

        return CommonResponse::ok($data, "Statistik tren penyakit periode $startDate s/d $endDate berhasil diambil");
    }

    /**
     * 4. Pendapatan dan Pengeluaran (Line Charts)
     */
    public function pendapatanPengeluaran(Request $request)
    {
        // 1. Ambil Parameter
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
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
        $queryPendapatan = \App\Models\RekamMedis::join('layanans', 'rekam_medis.kode_layanan', '=', 'layanans.id')
            ->selectRaw("$selectRawPendapatan, SUM(layanans.harga) as total")
            ->groupByRaw($groupByPendapatan);

        if ($startDate && $endDate) {
            $queryPendapatan->whereBetween('rekam_medis.created_at', [$startDate, $endDate]);
        }
        $pendapatan = $queryPendapatan->pluck('total', 'waktu')->toArray();

        // 5. Gabungkan & Sort
        $semuaWaktu = array_unique(array_merge(array_keys($pengeluaran), array_keys($pendapatan)));
        
        $semuaWaktu = array_filter($semuaWaktu, function($value) {
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
            } 
            elseif ($periode == 'bulanan') {
                try {
                    $displayText = \Carbon\Carbon::createFromFormat('Y-m', $waktu)->format('M Y');
                } catch (\Exception $e) { $displayText = $waktu; }
            }
            elseif ($periode == 'harian') {
                try {
                    $displayText = \Carbon\Carbon::parse($waktu)->format('d M Y');
                } catch (\Exception $e) { $displayText = $waktu; }
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
        ], "Data berhasil diambil");
    }

    /**
     * 5. Margin Keuntungan
     */
   public function marginKeuntungan(Request $request)
{
    [$startDate, $endDate] = $this->getFilterDates($request);

      // ======================
    // 1. TOTAL MODAL
    // ======================
    $totalModal = 50000000; // contoh total pengeluaran operasional

    // ======================
    // 2. TOTAL PENDAPATAN
    // ======================
    $totalPendapatan = 62500000; // contoh total pendapatan klinik

    // ======================
    // 3. HITUNG MARGIN
    // ======================
    $marginNominal = $totalPendapatan - $totalModal;

    $marginPercentage = $totalModal > 0
        ? round(($marginNominal / $totalModal) * 100)
        : 0;

    // ======================
    // 4. LABEL
    // ======================
    if ($marginNominal > 0) {
        $label = "Positif";
    } elseif ($marginNominal < 0) {
        $label = "Negatif";
    } else {
        $label = "Impas";
    }

    // ======================
    // 5. RESPONSE DATA
    // ======================
    $data = [
        'total_modal' => $totalModal,
        'margin_nominal' => $marginNominal,
        'margin_percentage' => $marginPercentage,
        'label' => $label,
    ];

    return CommonResponse::ok(
        $data,
        "Data margin periode $startDate s/d $endDate berhasil diambil"
    );
}


    /**
     * 6. Inventory Turnover Rate (Bar Chart per Kategori Obat)
     */
    public function inventoryTurnoverRate(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        $data = [
            'categories' => [], // Eloquent: Rasio per kategori (COGS / Avg Inventory)
        ];

        return CommonResponse::ok($data, "Statistik inventory turnover periode $startDate s/d $endDate berhasil diambil");
    }
}
