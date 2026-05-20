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
        $savedSession = \App\Models\DeteksiSession::where('user_id', Auth::id())->first();
        return view('karyawan.deteksi.index', compact('savedSession'));
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

        $answers = Session::get('deteksi_answers', []);
        $answeredCodes = array_keys($answers);
        $totalGejalaCount = Gejala::count();

        // TRUE BACKWARD CHAINING: Cek apakah hipotesis sudah terbukti sebelum lanjut tanya
        if (!empty($answers)) {
            $currentResult = $this->expertSystem->solve($answers);
            // Hanya berhenti prematur jika sistem SANGAT YAKIN (CF >= 0.85)
            if ($currentResult['cf'] >= 0.85 && $currentResult['cf'] > 0) {
                return $this->processResult(); 
            }
        }

        // Ambil gejala berikutnya berdasarkan logika Backward Chaining
        $nextCodes = $this->expertSystem->getNextSymptoms($answeredCodes);

        // Jika tidak ada lagi gejala yang perlu ditanyakan, proses hasil
        if (empty($nextCodes) && !empty($answeredCodes)) {
            return $this->processResult();
        }

        $questions = Gejala::whereIn('kode', $nextCodes)->get();

        // Jika benar-benar kosong (baru mulai), ambil beberapa gejala pertama
        if ($questions->isEmpty()) {
            $questions = Gejala::take(4)->get();
        }

        // Terapkan bahasa empati yang hangat pada pertanyaan secara dinamis
        foreach ($questions as $q) {
            $q->nama = $this->expertSystem->getEmpatheticPhrasing($q->kode, $q->nama);
        }

        return view('karyawan.deteksi.form', [
            'questions' => $questions,
            'question_codes' => $questions->pluck('kode')->toArray(),
            'progress' => count($answeredCodes),
            'total_gejala' => $totalGejalaCount,
            'progress_percent' => $totalGejalaCount > 0 ? round((count($answeredCodes) / $totalGejalaCount) * 100) : 0,
            'options' => [
                'Ya' => 'Ya, Sering Merasakan',
                'Tidak' => 'Tidak Pernah'
            ]
        ]);
    }

    /**
     * Menyimpan sesi deteksi burnout secara persisten ke database
     */
    public function saveSession(Request $request)
    {
        $answers = Session::get('deteksi_answers', []);

        if (empty($answers)) {
            return redirect()->route('karyawan.dashboard')->with('info', 'Tidak ada progres deteksi yang perlu disimpan.');
        }

        \App\Models\DeteksiSession::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'answers' => $answers,
                'current_step_codes' => []
            ]
        );

        Session::forget('deteksi_answers');

        return redirect()->route('karyawan.dashboard')->with('success', 'Sesi deteksi Anda berhasil disimpan secara aman! Anda dapat melanjutkannya kapan saja.');
    }

    /**
     * Memulihkan sesi deteksi burnout yang tersimpan secara persisten
     */
    public function resumeSession(Request $request)
    {
        $savedSession = \App\Models\DeteksiSession::where('user_id', Auth::id())->first();

        if ($savedSession) {
            Session::put('deteksi_answers', $savedSession->answers);
            $savedSession->delete();
            return redirect()->route('karyawan.deteksi')->with('success', 'Sesi deteksi Anda berhasil dipulihkan.');
        }

        return redirect()->route('karyawan.deteksi')->with('error', 'Tidak ada sesi tersimpan.');
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

        // Generate Explanation Facility
        $tracing = $konsultasi->tracing ?? [];
        $explanation = $this->expertSystem->generateExplanation(
            is_array($tracing) ? $tracing : [],
            $konsultasi->diagnosa,
            $konsultasi->cf_final
        );

        return view('karyawan.deteksi.hasil', [
            'konsultasi'  => $konsultasi,
            'confidence'  => number_format($konsultasi->cf_final * 100, 1),
            'tracing'     => $tracing,
            'explanation' => $explanation,
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

    /**
     * Download laporan deteksi (PDF Mock/Print View)
     */
    public function downloadReport($id)
    {
        $konsultasi = \App\Models\Konsultasi::with(['diagnosa', 'gejala', 'user'])->find($id);

        if (!$konsultasi || ($konsultasi->user_id !== Auth::id() && !Auth::user()->isHrd())) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Data tidak ditemukan.');
        }

        $tracing = $konsultasi->tracing ?? [];
        $explanation = $this->expertSystem->generateExplanation(
            is_array($tracing) ? $tracing : [],
            $konsultasi->diagnosa,
            $konsultasi->cf_final
        );

        return view('karyawan.deteksi.report', [
            'konsultasi' => $konsultasi,
            'confidence' => number_format($konsultasi->cf_final * 100, 1),
            'tracing' => $tracing,
            'explanation' => $explanation,
        ]);
    }
}
