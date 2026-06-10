<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDeteksiRequest;
use App\Models\Gejala;
use App\Models\Aturan;
use App\Models\Konsultasi;
use App\Services\ExpertSystemService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DeteksiController extends Controller
{
    protected $expertSystem;

    public function __construct(ExpertSystemService $expertSystem)
    {
        $this->expertSystem = $expertSystem;
    }

    /**
     * Halaman intro check-in kerja.
     */
    public function intro()
    {
        $savedSession = \App\Models\DeteksiSession::where('user_id', Auth::id())->first();
        return view('karyawan.deteksi.index', compact('savedSession'));
    }

    /**
     * Tampilkan halaman check-in kerja (Wizard).
     */
    public function index()
    {
        if (!Session::has('deteksi_answers')) {
            Session::put('deteksi_answers', []);
        }

        $answers = Session::get('deteksi_answers', []);
        $answeredCodes = array_keys($answers);
        $totalGejalaCount = Gejala::count();

        /**
         * TRUE BACKWARD CHAINING:
         * Sistem boleh berhenti prematur hanya jika:
         * - Ada hasil rule yang valid.
         * - CF melewati threshold dinamis rule.
         * - Tetap melewati batas early stop aman.
         * - Jumlah jawaban minimum sudah cukup.
         */
        if (!empty($answers)) {
            $currentResult = $this->expertSystem->solve($answers);

            if ($this->shouldStopEarly($currentResult, $answers)) {
                return $this->processResult();
            }
        }

        $nextCodes = $this->expertSystem->getNextSymptoms($answeredCodes);

        if (empty($nextCodes) && !empty($answeredCodes)) {
            return $this->processResult();
        }

        $questions = Gejala::whereIn('kode', $nextCodes)->get();

        if ($questions->isEmpty()) {
            $questions = Gejala::take(4)->get();
        }

        foreach ($questions as $q) {
            $q->nama = $this->expertSystem->getEmpatheticPhrasing($q->kode, $q->nama);
        }

        return view('karyawan.deteksi.form', [
            'questions' => $questions,
            'question_codes' => $questions->pluck('kode')->toArray(),
            'progress' => count($answeredCodes),
            'total_gejala' => $totalGejalaCount,
            'progress_percent' => $totalGejalaCount > 0
                ? round((count($answeredCodes) / $totalGejalaCount) * 100)
                : 0,
            'options' => [
                'Ya' => 'Ya, Sering Merasakan',
                'Tidak' => 'Tidak Pernah',
            ],
        ]);
    }

    /**
     * Menentukan apakah proses deteksi boleh dihentikan lebih awal.
     */
    private function shouldStopEarly(array $result, array $answers): bool
    {
        $cf = (float) ($result['cf'] ?? 0.0);
        $ruleCode = data_get($result, 'tracing.rule_kode');

        if (!$ruleCode || $cf <= 0) {
            return false;
        }

        $minThreshold = Aturan::query()
            ->where('kode', $ruleCode)
            ->where('is_active', true)
            ->value('min_threshold');

        $dynamicRuleThreshold = (float) ($minThreshold ?? 0.25);
        $earlyStopThreshold = max($dynamicRuleThreshold, 0.85);
        $minimumAnsweredSymptoms = 4;

        return count($answers) >= $minimumAnsweredSymptoms
            && $cf >= $earlyStopThreshold;
    }

    /**
     * Menyimpan sesi check-in secara persisten ke database.
     */
    public function saveSession(Request $request)
    {
        $answers = Session::get('deteksi_answers', []);

        if (empty($answers)) {
            return redirect()->route('karyawan.dashboard')->with('info', 'Belum ada progres check-in yang perlu disimpan.');
        }

        \App\Models\DeteksiSession::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'answers' => $answers,
                'current_step_codes' => []
            ]
        );

        Session::forget('deteksi_answers');

        return redirect()->route('karyawan.dashboard')->with('success', 'Progres check-in berhasil disimpan. Anda dapat melanjutkannya kapan saja.');
    }

    /**
     * Memulihkan sesi check-in yang tersimpan secara persisten.
     */
    public function resumeSession(Request $request)
    {
        $savedSession = \App\Models\DeteksiSession::where('user_id', Auth::id())->first();

        if ($savedSession) {
            Session::put('deteksi_answers', $savedSession->answers);
            $savedSession->delete();
            return redirect()->route('karyawan.deteksi')->with('success', 'Progres check-in berhasil dipulihkan.');
        }

        return redirect()->route('karyawan.deteksi')->with('error', 'Tidak ada sesi check-in tersimpan.');
    }

    /**
     * Simpan jawaban sementara dan lanjut ke langkah berikutnya.
     */
    public function next(StoreDeteksiRequest $request)
    {
        $request->validated();

        $answers = Session::get('deteksi_answers', []);

        foreach ($request->except('_token', 'gejala_id') as $kode => $value) {
            if (str_starts_with($kode, 'G')) {
                $answers[$kode] = $value;
            }
        }

        /**
         * Dukungan aman untuk payload alternatif berbasis gejala_id[].
         * Field ini sudah divalidasi oleh StoreDeteksiRequest.
         */
        if ($request->filled('gejala_id')) {
            $selectedCodes = Gejala::query()
                ->whereIn('id', $request->input('gejala_id', []))
                ->pluck('kode')
                ->all();

            foreach ($selectedCodes as $kode) {
                $answers[$kode] = 'Ya';
            }
        }

        Session::put('deteksi_answers', $answers);

        return redirect()->route('karyawan.deteksi');
    }

    /**
     * Hitung hasil akhir deteksi.
     */
    protected function processResult()
    {
        $answers = Session::get('deteksi_answers', []);

        if (empty($answers)) {
            return redirect()
                ->route('karyawan.deteksi')
                ->with('error', 'Belum ada jawaban yang dapat dianalisis. Silakan isi check-in terlebih dahulu.');
        }

        $result = $this->expertSystem->solve($answers);

        /**
         * Seluruh proses penyimpanan konsultasi, tracing, dan pivot gejala
         * dibungkus transaction agar tidak ada data setengah tersimpan.
         */
        $konsultasi = DB::transaction(function () use ($result, $answers) {
            return $this->expertSystem->saveResult(Auth::id(), $result, $answers);
        });

        NotificationService::dispatchAfterDeteksi($konsultasi, Auth::user(), $result['diagnosa']);

        Session::put('last_result_id', $konsultasi->id);
        Session::forget('deteksi_answers');
        Session::forget('bc_engine.current_hypothesis');

        return redirect()->route('karyawan.hasil');
    }

    /**
     * Tampilkan halaman hasil deteksi.
     * Halaman ini hanya membaca record historis Konsultasi yang sudah fixed.
     */
    public function showResult(Request $request)
    {
        $id = $request->id ?? Session::get('last_result_id');

        if (!$id) {
            return redirect()->route('karyawan.dashboard');
        }

        $konsultasi = Konsultasi::with(['diagnosa', 'gejala', 'user'])->find($id);

        if (!$konsultasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Data hasil check-in tidak ditemukan.');
        }

        abort_if((int) $konsultasi->user_id !== (int) Auth::id(), 403);

        $tracing = $konsultasi->tracing ?? [];
        $currentResult = [
            'tracing' => is_array($tracing) ? $tracing : [],
        ];

        $explanation = $this->expertSystem->generateExplanation(
            $currentResult['tracing'] ?? [],
            $konsultasi->diagnosa,
            $konsultasi->cf_final
        );

        return view('karyawan.deteksi.hasil', [
            'konsultasi'  => $konsultasi,
            'confidence'  => number_format($konsultasi->cf_final * 100, 1),
            'tracing'     => $currentResult['tracing'] ?? [],
            'explanation' => $explanation,
        ]);
    }

    /**
     * Reset sesi deteksi dan mulai ulang dengan form kosong.
     */
    public function reset()
    {
        Session::forget('deteksi_answers');
        Session::forget('last_result_id');
        Session::forget('bc_engine.current_hypothesis');

        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Sesi lama sudah dibersihkan. Silakan mulai check-in baru dari awal.');
    }

    /**
     * Download laporan deteksi (PDF Mock/Print View).
     */
    public function downloadReport($id)
    {
        $konsultasi = Konsultasi::with(['diagnosa', 'gejala', 'user'])->find($id);

        if (!$konsultasi) {
            return redirect()->route('karyawan.dashboard')->with('error', 'Data tidak ditemukan.');
        }

        abort_if((int) $konsultasi->user_id !== (int) Auth::id(), 403);

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
