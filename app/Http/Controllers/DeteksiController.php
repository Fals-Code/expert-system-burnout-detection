<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Services\ExpertSystemService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DeteksiController extends Controller
{
    protected $expertSystem;

    public function __construct(ExpertSystemService $expertSystem)
    {
        $this->expertSystem = $expertSystem;
    }

    /**
     * Halaman intro deteksi
     */
    public function intro()
    {
        return view('karyawan.deteksi.index');
    }

    /**
     * Tampilkan halaman deteksi (Wizard)
     */
    public function index()
    {
        // Jika belum ada session deteksi, inisialisasi
        if (!Session::has('deteksi_answers')) {
            Session::put('deteksi_answers', []);
        }

        $answers = Session::get('deteksi_answers');
        $answeredCodes = array_keys($answers);

        // Ambil gejala berikutnya berdasarkan logika Backward Chaining
        $nextCodes = $this->expertSystem->getNextSymptoms($answeredCodes);

        // Jika tidak ada lagi gejala yang perlu ditanyakan, proses hasil
        if (empty($nextCodes) && !empty($answeredCodes)) {
            return $this->processResult();
        }

        $questions = Gejala::whereIn('kode', $nextCodes)->get();

        // Jika benar-benar kosong (baru mulai), ambil beberapa gejala pertama
        if ($questions->isEmpty()) {
            $questions = Gejala::take(5)->get();
        }

        return view('karyawan.deteksi.form', [
            'questions' => $questions,
            'question_codes' => $questions->pluck('kode')->toArray(),
            'progress' => count($answeredCodes),
            'total_gejala' => Gejala::count(),
            'options' => [
                'Sangat Sering' => 'Pasti Ya / Sangat Sering',
                'Sering' => 'Ya / Sering',
                'Kadang' => 'Mungkin / Kadang-kadang',
                'Jarang' => 'Ragu-ragu / Jarang',
                'Sangat Jarang' => 'Sedikit / Sangat Jarang',
                'Tidak' => 'Tidak Pernah'
            ]
        ]);
    }

    /**
     * Simpan jawaban sementara dan lanjut ke langkah berikutnya
     */
    public function next(Request $request)
    {
        $answers = Session::get('deteksi_answers', []);
        
        foreach ($request->except('_token') as $kode => $value) {
            if (str_starts_with($kode, 'G')) {
                $answers[$kode] = $value;
            }
        }

        Session::put('deteksi_answers', $answers);

        return redirect()->route('karyawan.deteksi');
    }

    /**
     * Hitung hasil akhir deteksi
     */
    protected function processResult()
    {
        $answers = Session::get('deteksi_answers', []);
        
        // Jalankan Engine Sistem Pakar
        $result = $this->expertSystem->solve($answers);
        
        // Simpan ke Database
        $konsultasi = $this->expertSystem->saveResult(Auth::id(), $result, $answers);

        // Notifikasi
        NotificationService::dispatchAfterDeteksi($konsultasi, Auth::user(), $result['diagnosa']);

        // Simpan ke Session untuk tampilan hasil
        Session::put('last_result_id', $konsultasi->id);
        Session::forget('deteksi_answers');

        return redirect()->route('karyawan.hasil');
    }

    /**
     * Tampilkan halaman hasil deteksi
     */
    public function showResult(Request $request)
    {
        $id = $request->id ?? Session::get('last_result_id');
        
        if (!$id) return redirect()->route('karyawan.dashboard');

        $konsultasi = \App\Models\Konsultasi::with(['diagnosa', 'gejala', 'user'])->find($id);

        if (!$konsultasi || ($konsultasi->user_id !== Auth::id() && !Auth::user()->isHrd())) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Data tidak ditemukan.');
        }

        return view('karyawan.deteksi.hasil', [
            'konsultasi' => $konsultasi,
            'confidence' => number_format($konsultasi->cf_final * 100, 1),
            'tracing' => $konsultasi->tracing, // JSON Tracing
        ]);
    }

    /**
     * Reset sesi deteksi
     */
    public function reset()
    {
        Session::forget('deteksi_answers');
        Session::forget('last_result_id');
        return redirect()->route('karyawan.dashboard')->with('info', 'Sesi deteksi telah direset.');
    }
}
