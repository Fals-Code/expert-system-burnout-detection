<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gejala;
use App\Models\Aturan;
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

    public function index()
    {
        $user = Auth::user();
        
        // Reset jika tidak ada state pending
        if (!Session::has('bc_engine.pending_questions')) {
            $this->initializeEngine();
        }

        $engine = Session::get('bc_engine');
        $pendingGids = $engine['pending_questions'];
        
        $gejala = Gejala::whereIn('kode', $pendingGids)->get();
        
        // Map questions
        $questions = [];
        foreach ($gejala as $g) {
            $questions[$g->kode] = "Seberapa sering Anda mengalami: " . $g->nama . "?";
        }

        $data = [
            'questions' => $questions,
            'total_questions' => count($questions),
            'current_hypothesis' => $engine['current_hypothesis'],
            'current_goal_index' => $engine['current_goal_index'],
        ];

        return view('karyawan.deteksi', $data);
    }

    public function process(Request $request)
    {
        $engine = Session::get('bc_engine');
        $answers = $engine['answers'];

        // Simpan jawaban baru
        foreach ($request->all() as $key => $val) {
            if (preg_match('/^G\d{3}$/', $key) && in_array($val, ['Sering', 'Kadang', 'Tidak Pernah'])) {
                $answers[$key] = $val;
            }
        }

        $engine['answers'] = $answers;
        Session::put('bc_engine', $engine);

        return $this->runEngine();
    }

    protected function initializeEngine()
    {
        $bc_goals = ['BERAT', 'SEDANG', 'RINGAN']; // Tingkat diagnosa
        
        // Ambil aturan untuk goal pertama (BERAT)
        $initialRules = Aturan::whereHas('diagnosa', function($q) {
            $q->where('tingkat', 'BERAT');
        })->with('gejala')->get();

        $initialGids = [];
        foreach ($initialRules as $rule) {
            foreach ($rule->gejala as $g) {
                if (!in_array($g->kode, $initialGids)) $initialGids[] = $g->kode;
            }
        }

        Session::put('bc_engine', [
            'goal_index' => 0,
            'answers' => [],
            'bc_trace' => [],
            'current_hypothesis' => 'Fase 1',
            'current_goal_index' => 0,
            'pending_questions' => $initialGids,
        ]);
    }

    protected function runEngine()
    {
        $engine = Session::get('bc_engine');
        $bc_goals = ['BERAT', 'SEDANG', 'RINGAN'];
        $cf_threshold = 0.25;

        while ($engine['goal_index'] < count($bc_goals)) {
            $currentGoal = $bc_goals[$engine['goal_index']];

            $goalRules = Aturan::whereHas('diagnosa', function($q) use ($currentGoal) {
                $q->where('tingkat', $currentGoal);
            })->with('gejala')->get();

            if ($goalRules->isEmpty()) {
                $engine['goal_index']++;
                continue;
            }

            $answeredCodes = array_keys($engine['answers']);
            $needed = $this->expertSystem->getNeededSymptoms($goalRules->all(), $answeredCodes);

            if (!empty($needed)) {
                $engine['pending_questions'] = $needed;
                $engine['current_hypothesis'] = "Fase " . ($engine['goal_index'] + 1);
                $engine['current_goal_index'] = $engine['goal_index'];
                Session::put('bc_engine', $engine);
                return redirect()->route('karyawan.deteksi');
            }

            // Semua gejala untuk goal ini sudah dijawab, evaluasi!
            [$highestCf, $bestRule, $tracing] = $this->expertSystem->evaluateHypothesis($goalRules, $engine['answers']);

            $engine['bc_trace'][] = [
                'goal' => $currentGoal,
                'cf_final' => $highestCf,
                'confirmed' => $highestCf >= $cf_threshold
            ];

            if ($highestCf >= $cf_threshold) {
                try {
                    // Konfirmasi!
                    $diagnosa = $bestRule->diagnosa;
                    
                    // Simpan ke DB
                    $gejalaCodesUsed = [];
                    foreach ($bestRule->gejala as $g) {
                        if (($engine['answers'][$g->kode] ?? 'Tidak Pernah') !== 'Tidak Pernah') {
                            $gejalaCodesUsed[] = $g->kode;
                        }
                    }

                    $konsultasi = $this->expertSystem->saveResult(Auth::id(), $diagnosa->id, $highestCf, $gejalaCodesUsed);

                    // ── Kirim notifikasi otomatis pasca deteksi ──
                    NotificationService::dispatchAfterDeteksi($konsultasi, Auth::user(), $diagnosa);

                    // Simpan hasil ke session untuk tampilan hasil
                    Session::put('hasil_deteksi', [
                        'id' => $konsultasi->id,
                        'diagnosa' => $diagnosa,
                        'cf_final' => $highestCf,
                        'tracing' => $tracing,
                        'gejala_terdeteksi' => $gejalaCodesUsed,
                        'bc_trace' => $engine['bc_trace']
                    ]);

                    Session::forget('bc_engine');
                    return redirect()->route('karyawan.hasil');
                } catch (\Exception $e) {
                    return redirect()->route('karyawan.dashboard')->with('error', 'Terjadi kesalahan sistem saat memproses hasil Anda: ' . $e->getMessage());
                }
            }

            $engine['goal_index']++;
        }

        // Tidak ada yang cocok
        Session::forget('bc_engine');
        return redirect()->route('karyawan.hasil')->with('no_burnout', true);
    }

    public function showResult(Request $request)
    {
        $hasil = null;
        
        if ($request->has('id')) {
            $konsultasi = \App\Models\Konsultasi::with(['diagnosa', 'gejala'])->find($request->id);
            if ($konsultasi && $konsultasi->user_id === Auth::id()) {
                $hasil = [
                    'id' => $konsultasi->id,
                    'diagnosa' => $konsultasi->diagnosa,
                    'cf_final' => $konsultasi->cf_final,
                    'tracing' => [
                        'rule_id' => 'Historical Record',
                        'details' => ['Perhitungan dilakukan pada ' . $konsultasi->created_at->format('d/m/Y H:i')],
                        'cf_final' => $konsultasi->cf_final
                    ],
                    'gejala_terdeteksi' => $konsultasi->gejala->pluck('kode')->toArray(),
                    'bc_trace' => []
                ];
            }
        }

        if (!$hasil) {
            if (!Session::has('hasil_deteksi')) {
                if (Session::has('no_burnout')) {
                    return view('karyawan.hasil', ['no_burnout' => true]);
                }
                return redirect()->route('karyawan.deteksi');
            }
            $hasil = Session::get('hasil_deteksi');
        }

        $diagnosa = $hasil['diagnosa'];
        $data = [
            'level' => $diagnosa->tingkat,
            'label' => $diagnosa->nama,
            'confidence' => min(99, max(10, intval($hasil['cf_final'] * 100))),
            'color' => $diagnosa->color,
            'bg_light' => $diagnosa->bg_light,
            'desc' => $diagnosa->deskripsi,
            'gejala_terdeteksi' => Gejala::whereIn('kode', $hasil['gejala_terdeteksi'])->pluck('nama')->toArray(),
            'rekomendasi' => $this->getRecommendations($diagnosa->tingkat),
            'tanggal' => now()->translatedFormat('d F Y'),
            'tracing' => $hasil['tracing'],
            'bc_trace' => $hasil['bc_trace'],
        ];

        return view('karyawan.hasil', $data);
    }

    protected function getRecommendations($tingkat)
    {
        $rekomendasi_map = [
            'BERAT' => [
                ['icon' => '🧘', 'judul' => 'Konseling Psikolog', 'isi' => 'Sangat disarankan untuk segera berkonsultasi dengan psikolog klinis profesional.'],
                ['icon' => '✈️', 'judul' => 'Ambil Cuti Terencana', 'isi' => 'Istirahat total sangat diperlukan untuk memulihkan kondisi fisik dan mental.'],
            ],
            'SEDANG' => [
                ['icon' => '⚖️', 'judul' => 'Manajemen Waktu', 'isi' => 'Prioritaskan tugas penting dan delegasikan tugas jika memungkinkan. Atur jadwal istirahat rutin.'],
                ['icon' => '🌿', 'judul' => 'Relaksasi Rutin', 'isi' => 'Lakukan meditasi atau hobi yang menenangkan setiap akhir pekan.'],
            ],
            'RINGAN' => [
                ['icon' => '😴', 'judul' => 'Jaga Kualitas Tidur', 'isi' => 'Pastikan tidur 7-9 jam setiap malam dan kurangi paparan layar sebelum tidur.'],
                ['icon' => '🏃', 'judul' => 'Aktivitas Fisik', 'isi' => 'Olahraga ringan 15-30 menit dapat membantu mengurangi stres ringan.'],
            ],
        ];
        return $rekomendasi_map[$tingkat] ?? [];
    }
    /**
     * Reset the ongoing detection session and return to dashboard.
     */
    public function reset()
    {
        Session::forget('bc_engine');
        Session::forget('hasil_deteksi');
        return redirect()->route('karyawan.dashboard')->with('info', 'Sesi deteksi telah direset.');
    }
}
