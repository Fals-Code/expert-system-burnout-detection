<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ExpertSystemService
{
    /**
     * Certainty Factor Mapping (CF_User)
     * Mengubah jawaban linguistik menjadi nilai kepastian kuantitatif.
     */
    public function getCfUser($answer): float
    {
        return match ($answer) {
            'Sangat Sering', 'Ya', 'Pasti Ya' => 1.0,
            'Sering', 'Hampir Pasti'          => 0.8,
            'Kadang'                          => 0.6,
            'Mungkin'                         => 0.5,
            'Jarang'                          => 0.4,
            'Ragu-ragu'                       => 0.3,
            'Sangat Jarang', 'Sedikit'        => 0.2,
            'Tidak', 'Tidak Pernah'            => 0.0,
            default                            => 0.0,
        };
    }

    /**
     * Mengarahkan nilai CF user berdasarkan arah evidence pada pivot aturan_gejala.
     *
     * PRESENT_SUPPORTS:
     * - Jawaban Ya mendukung rule.
     * - Jawaban Tidak tidak mendukung rule.
     *
     * ABSENT_SUPPORTS:
     * - Jawaban Tidak mendukung rule.
     * - Jawaban Ya tidak mendukung rule.
     */
    public function getDirectedCfUser($answer, string $evidenceDirection = 'PRESENT_SUPPORTS'): float
    {
        $rawCf = $this->getCfUser($answer);

        return match ($evidenceDirection) {
            'ABSENT_SUPPORTS' => 1.0 - $rawCf,
            default => $rawCf,
        };
    }

    /**
     * Backward Chaining & Certainty Factor Engine dengan Conflict Resolution,
     * Priority, Rule Validation, dan Directional Evidence.
     *
     * @param array $answers Format: ['G01' => 'Ya', 'G02' => 'Tidak', ...]
     * @param string $conflictStrategy highest_cf|first_matched|priority_based
     * @return array
     */
    public function solve(array $answers, string $conflictStrategy = 'highest_cf'): array
    {
        $conflictStrategy = in_array($conflictStrategy, [
            'highest_cf',
            'first_matched',
            'priority_based',
        ], true) ? $conflictStrategy : 'highest_cf';

        $serializedRules = Cache::remember('aturan_active_rules_base64', 86400, function () {
            return base64_encode(serialize(
                Aturan::where('is_active', true)
                    ->with(['diagnosa', 'gejala'])
                    ->get()
            ));
        });

        $rules = collect(unserialize(base64_decode($serializedRules)));

        if ($rules->isEmpty()) {
            return $this->defaultResult('Tidak ada aturan aktif di basis pengetahuan.', $answers);
        }

        $triggeredRules = [];

        foreach ($rules as $rule) {
            $gejalaList = $rule->gejala;

            if ($gejalaList->isEmpty()) {
                continue;
            }

            $cfCombine = 0.0;
            $ruleTrace = [];
            $answeredCount = 0;

            foreach ($gejalaList as $gejala) {
                $userAnswer = 'Belum Dijawab';
                $rawCfUser = 0.0;
                $cfUser = 0.0;

                $bobotPakar = (float) (
                    $gejala->pivot->bobot_pakar
                    ?? $gejala->bobot
                    ?? 0.0
                );

                $evidenceDirection = $gejala->pivot->evidence_direction
                    ?? 'PRESENT_SUPPORTS';

                if (isset($answers[$gejala->kode])) {
                    $answeredCount++;

                    $userAnswer = $answers[$gejala->kode];
                    $rawCfUser = $this->getCfUser($userAnswer);
                    $cfUser = $this->getDirectedCfUser($userAnswer, $evidenceDirection);
                }

                $cfWeighted = $cfUser * $bobotPakar;
                $cfCombine = $this->combineCf($cfCombine, $cfWeighted);

                $ruleTrace[] = [
                    'gejala' => $gejala->nama,
                    'kode' => $gejala->kode,
                    'kategori' => $gejala->kategori,
                    'user_ans' => $userAnswer,
                    'raw_cf_user' => $rawCfUser,
                    'cf_user' => $cfUser,
                    'bobot' => $bobotPakar,
                    'cf_sub' => $cfWeighted,
                    'evidence_direction' => $evidenceDirection,
                ];
            }

            $cfPakarRule = (float) ($rule->cf_pakar ?? 0.0);
            $minThreshold = (float) ($rule->min_threshold ?? 0.25);
            $cfFinalRule = $cfCombine * $cfPakarRule;

            $isValid = $answeredCount > 0 && $cfFinalRule >= $minThreshold;

            if ($isValid) {
                $triggeredRules[] = [
                    'rule' => $rule,
                    'cf_final' => $cfFinalRule,
                    'cf_combine' => $cfCombine,
                    'trace_details' => $ruleTrace,
                    'prioritas' => (int) ($rule->prioritas ?? 0),
                    'diagnosa' => $rule->diagnosa,
                    'min_threshold' => $minThreshold,
                ];
            }
        }

        if (empty($triggeredRules)) {
            return $this->defaultResult(
                'Tidak ada aturan yang mencapai ambang batas minimum. Sistem mengembalikan kondisi default sehat/tidak burnout.',
                $answers
            );
        }

        switch ($conflictStrategy) {
            case 'priority_based':
                usort($triggeredRules, function ($a, $b) {
                    if ($b['prioritas'] === $a['prioritas']) {
                        return $b['cf_final'] <=> $a['cf_final'];
                    }

                    return $b['prioritas'] <=> $a['prioritas'];
                });
                break;

            case 'first_matched':
                usort($triggeredRules, function ($a, $b) {
                    return $a['diagnosa']->kode <=> $b['diagnosa']->kode;
                });
                break;

            case 'highest_cf':
            default:
                usort($triggeredRules, function ($a, $b) {
                    return $b['cf_final'] <=> $a['cf_final'];
                });
                break;
        }

        $winner = $triggeredRules[0];

        return [
            'diagnosa' => $winner['diagnosa'],
            'cf' => $winner['cf_final'],
            'tracing' => [
                'rule_kode' => $winner['rule']->kode,
                'cf_pakar_rule' => $winner['rule']->cf_pakar,
                'cf_combine_gejala' => $winner['cf_combine'],
                'min_threshold' => $winner['min_threshold'],
                'gejala_details' => $winner['trace_details'],
                'prioritas' => $winner['prioritas'],
                'conflict_strategy' => $conflictStrategy,
                'method' => 'Backward Chaining (Directional Evidence + CF Combine)',
                'all_candidate_rules' => collect($triggeredRules)->map(function ($item) {
                    return [
                        'rule_kode' => $item['rule']->kode,
                        'diagnosa' => $item['diagnosa']->nama,
                        'cf_final' => round($item['cf_final'], 4),
                        'prioritas' => $item['prioritas'],
                        'min_threshold' => $item['min_threshold'],
                    ];
                })->toArray(),
            ],
        ];
    }

    /**
     * Hasil default ketika tidak ada rule burnout yang cukup kuat.
     * Karena D01 sekarang adalah Tidak Burnout, fallback harus mengarah ke D01.
     */
    protected function defaultResult(string $reason, array $answers = []): array
    {
        $serializedDefault = Cache::remember('diagnosa_default_tidak_burnout_base64', 86400, function () {
            $diagnosa = Diagnosa::where('kode', 'D01')
                ->orWhere('tingkat', 'TIDAK BURNOUT')
                ->orWhere('nama', 'like', '%Tidak Burnout%')
                ->first();

            return $diagnosa ? base64_encode(serialize($diagnosa)) : null;
        });

        $defaultDiagnosa = $serializedDefault
            ? unserialize(base64_decode($serializedDefault))
            : null;

        $defaultDiagnosa = $defaultDiagnosa ?? Diagnosa::make([
            'id' => 1,
            'kode' => 'D01',
            'nama' => 'Tidak Burnout (Kondisi Sehat)',
            'tingkat' => 'TIDAK BURNOUT',
            'deskripsi' => 'Tidak ditemukan pola gejala burnout yang signifikan.',
            'saran' => 'Pertahankan pola kerja sehat, istirahat cukup, dan keseimbangan aktivitas harian.',
        ]);

        $gejalaDetails = [];

        if (!empty($answers)) {
            $answeredKodes = array_keys($answers);

            $gejalaList = Gejala::whereIn('kode', $answeredKodes)
                ->get()
                ->keyBy('kode');

            foreach ($answers as $kode => $userAnswer) {
                $gejala = $gejalaList[$kode] ?? null;

                if (!$gejala) {
                    continue;
                }

                $rawCfUser = $this->getCfUser($userAnswer);
                $directedCfUser = $this->getDirectedCfUser($userAnswer, 'ABSENT_SUPPORTS');
                $bobot = (float) ($gejala->bobot ?? 0.5);

                $gejalaDetails[] = [
                    'gejala' => $gejala->nama,
                    'kode' => $gejala->kode,
                    'kategori' => $gejala->kategori ?? 'emosional',
                    'user_ans' => $userAnswer,
                    'raw_cf_user' => $rawCfUser,
                    'cf_user' => $directedCfUser,
                    'bobot' => $bobot,
                    'cf_sub' => $directedCfUser * $bobot,
                    'evidence_direction' => 'ABSENT_SUPPORTS',
                ];
            }
        }

        return [
            'diagnosa' => $defaultDiagnosa,
            'cf' => 0.0,
            'tracing' => [
                'message' => $reason,
                'method' => 'Fallback Default Result - Tidak Burnout',
                'gejala_details' => $gejalaDetails,
            ],
        ];
    }

    /**
     * Empathetic symptom phrasing dictionary translating clinical names into highly compassionate questions.
     */
    public function getEmpatheticPhrasing(string $symptomCode, string $defaultName): string
    {
        $phrasings = [
            'G01' => 'Apakah Anda merasa kehabisan energi secara emosional dan fisik setelah menyelesaikan seharian bekerja?',
            'G02' => 'Apakah Anda merasa lemas, letih, dan tidak memiliki motivasi saat bangun pagi dan harus menghadapi hari kerja?',
            'G03' => 'Apakah Anda merasa tegang, tertekan, atau kewalahan akibat tuntutan pekerjaan yang terus bertumpuk?',
            'G04' => 'Apakah Anda merasa lebih rentan tersinggung, frustrasi, atau kehilangan kesabaran karena masalah-masalah kecil di kantor?',
            'G05' => 'Apakah Anda merasa semakin sinis, kurang bergairah, atau meragukan pentingnya kontribusi pekerjaan Anda?',
            'G06' => 'Apakah Anda merasa kurang peduli dengan rekan kerja, pelanggan, atau perkembangan masa depan tempat kerja Anda?',
            'G07' => 'Apakah Anda merasa ingin membatasi komunikasi, menjauhkan diri, atau mengisolasi diri dari aktivitas sosial kantor?',
            'G08' => 'Apakah Anda merasa apa yang Anda kerjakan sia-sia dan tidak mendatangkan manfaat nyata bagi diri Anda maupun tim?',
            'G09' => 'Apakah Anda merasa sangat sulit fokus, sering melamun, atau melakukan kesalahan kecil pada tugas-tugas penting?',
            'G10' => 'Apakah Anda merasa kehilangan rasa percaya diri dan terus-menerus tidak puas dengan pencapaian kerja Anda?',
            'G11' => 'Apakah Anda sering merasakan keluhan fisik seperti nyeri lambung, mual, atau pusing kaku tanpa diagnosa medis?',
            'G12' => 'Apakah Anda mengalami kesulitan tidur pulas karena cemas, atau sebaliknya, selalu merasa lesu sepanjang hari?',
        ];

        return $phrasings[$symptomCode] ?? $defaultName;
    }

    /**
     * ADAPTIVE QUESTIONING (Hanya tanya gejala yang relevan)
     * Menggunakan Backward Chaining terarah dengan Rule Pruning dan strategi pemilihan gejala.
     *
     * @param array $answeredCodes Daftar kode gejala yang sudah dijawab
     * @param string $strategy Strategi pemilihan gejala: highest_cf_gain|most_common|diagnosis_order
     * @return array Daftar kode gejala berikutnya yang wajib ditanyakan
     */
    public function getNextSymptoms(array $answeredCodes, string $strategy = 'highest_cf_gain'): array
    {
        $strategy = in_array($strategy, ['highest_cf_gain', 'most_common', 'diagnosis_order'])
            ? $strategy
            : 'highest_cf_gain';

        $diagnosas = $this->getOrderedDiagnosas();
        $answers = $this->getSessionAnswers();

        foreach ($diagnosas as $diagnosa) {
            $rules = $this->getRulesForDiagnosa($diagnosa);
            [$candidateSymptoms, $fallbackUnanswered] = $this->buildSymptomCandidates(
                $rules,
                $answers,
                $answeredCodes
            );

            if (empty($candidateSymptoms)) {
                continue;
            }

            Session::put('bc_engine.current_hypothesis', 'Menganalisis Hipotesis: ' . $diagnosa->nama . ' (Backward Chaining)');
            return $this->rankCandidateSymptoms($candidateSymptoms, $strategy, $fallbackUnanswered);
        }

        return [];
    }

    /**
     * Ambil jawaban deteksi yang tersimpan di session.
     *
     * @return array<string, string>
     */
    private function getSessionAnswers(): array
    {
        return Session::get('deteksi_answers', []);
    }

    /**
     * Ambil diagnosa terurut dari cache untuk menghindari query berulang.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Diagnosa>
     */
    private function getOrderedDiagnosas()
    {
        $serializedDiagnosas = Cache::remember('diagnosa_ordered_base64', 86400, function () {
            return base64_encode(serialize(Diagnosa::orderBy('kode', 'asc')->get()));
        });

        return collect(unserialize(base64_decode($serializedDiagnosas)));
    }

    /**
     * Ambil aturan aktif untuk diagnosa tertentu dari cache.
     *
     * @param \App\Models\Diagnosa $diagnosa
     * @return \Illuminate\Support\Collection<int, \App\Models\Aturan>
     */
    private function getRulesForDiagnosa(Diagnosa $diagnosa)
    {
        $serializedRules = Cache::remember("aturan_by_diagnosa_{$diagnosa->id}_base64", 86400, function () use ($diagnosa) {
            return base64_encode(serialize(Aturan::where('diagnosa_id', $diagnosa->id)
                ->where('is_active', true)
                ->orderBy('prioritas', 'desc')
                ->with('gejala')
                ->get()));
        });

        return collect(unserialize(base64_decode($serializedRules)));
    }

    /**
     * Kumpulkan kandidat gejala dari aturan yang masih layak (feasible) untuk ditanyakan berikutnya.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Aturan> $rules
     * @param array<string, string> $answers
     * @param array $answeredCodes
     * @return array{0: array<string, array>, 1: array<string>}
     */
    private function buildSymptomCandidates($rules, array $answers, array $answeredCodes): array
    {
        $candidateSymptoms = [];
        $fallbackUnanswered = [];

        foreach ($rules as $rule) {
            $gejalaList = $rule->gejala;

            if ($gejalaList->isEmpty()) {
                continue;
            }

            $currentCfCombine = $this->calculateCurrentCfCombine($gejalaList, $answers);
            $optimisticCfCombine = $this->calculateOptimisticCfCombine($gejalaList, $answers);
            $cfPakarRule = (float) ($rule->cf_pakar ?? 0.0);
            $minThreshold = (float) ($rule->min_threshold ?? 0.25);
            $maxPossibleCf = $optimisticCfCombine * $cfPakarRule;

            if ($maxPossibleCf < $minThreshold) {
                continue;
            }

            $unanswered = $this->getUnansweredSymptoms($gejalaList, $answeredCodes);

            if ($unanswered->isEmpty()) {
                continue;
            }

            foreach ($unanswered as $gejala) {
                $bobotPakar = (float) (
                    $gejala->pivot->bobot_pakar
                    ?? $gejala->bobot
                    ?? 0.0
                );

                $potentialCfCombine = $this->combineCf($currentCfCombine, 1.0 * $bobotPakar);
                $estimatedGain = max(0.0, ($potentialCfCombine * $cfPakarRule) - ($currentCfCombine * $cfPakarRule));

                $candidateSymptoms[$gejala->kode] = $this->mergeSymptomCandidate(
                    $candidateSymptoms[$gejala->kode] ?? [],
                    $gejala,
                    $estimatedGain,
                    $bobotPakar,
                    $rule->kode
                );
            }

            $fallbackUnanswered = array_merge($fallbackUnanswered, $unanswered->pluck('kode')->toArray());
        }

        return [$candidateSymptoms, $fallbackUnanswered];
    }

    /**
     * Gabungkan data kandidat gejala ketika muncul di lebih dari satu aturan.
     *
     * @param array $existing
     * @param \App\Models\Gejala $gejala
     * @param float $estimatedGain
     * @param float $weight
     * @param string $ruleCode
     * @return array
     */
    private function mergeSymptomCandidate(array $existing, $gejala, float $estimatedGain, float $weight, string $ruleCode): array
    {
        return [
            'kode' => $gejala->kode,
            'nama' => $gejala->nama,
            'gain' => ($existing['gain'] ?? 0.0) + $estimatedGain,
            'frequency' => ($existing['frequency'] ?? 0) + 1,
            'max_weight' => max($existing['max_weight'] ?? 0.0, $weight),
            'rules' => array_unique(array_merge($existing['rules'] ?? [], [$ruleCode])),
        ];
    }

    /**
     * Ranking kandidat gejala berdasarkan strategi yang dipilih.
     *
     * @param array $candidateSymptoms
     * @param string $strategy
     * @param string[] $fallbackUnanswered
     * @return string[]
     */
    private function rankCandidateSymptoms(array $candidateSymptoms, string $strategy, array $fallbackUnanswered): array
    {
        if ($strategy === 'diagnosis_order') {
            return array_values(array_unique($fallbackUnanswered));
        }

        $symptomCollection = collect($candidateSymptoms);

        $ordered = $symptomCollection->sortByDesc(fn ($item) => [
            $strategy === 'most_common' ? $item['frequency'] : $item['gain'],
            $item['gain'],
            $item['max_weight'],
        ]);

        return $ordered->pluck('kode')->unique()->values()->toArray();
    }

    /**
     * Hitung kombinasi CF saat ini berdasarkan gejala yang sudah dijawab,
     * dengan memperhatikan arah evidence pada pivot.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Gejala> $gejalaList
     * @param array<string, string> $answers
     * @return float
     */
    private function calculateCurrentCfCombine($gejalaList, array $answers): float
    {
        $cfCombine = 0.0;

        foreach ($gejalaList as $gejala) {
            if (!isset($answers[$gejala->kode])) {
                continue;
            }

            $bobotPakar = (float) (
                $gejala->pivot->bobot_pakar
                ?? $gejala->bobot
                ?? 0.0
            );

            $evidenceDirection = $gejala->pivot->evidence_direction
                ?? 'PRESENT_SUPPORTS';

            $cfUser = $this->getDirectedCfUser(
                $answers[$gejala->kode],
                $evidenceDirection
            );

            $cfCombine = $this->combineCf($cfCombine, $cfUser * $bobotPakar);
        }

        return $cfCombine;
    }

    /**
     * Hitung kombinasi CF optimistik untuk gejala yang belum dijawab.
     * Untuk PRESENT_SUPPORTS, asumsi optimistik = Ya.
     * Untuk ABSENT_SUPPORTS, asumsi optimistik = Tidak.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Gejala> $gejalaList
     * @param array<string, string> $answers
     * @return float
     */
    private function calculateOptimisticCfCombine($gejalaList, array $answers): float
    {
        $cfCombine = $this->calculateCurrentCfCombine($gejalaList, $answers);

        foreach ($gejalaList as $gejala) {
            if (isset($answers[$gejala->kode])) {
                continue;
            }

            $bobotPakar = (float) (
                $gejala->pivot->bobot_pakar
                ?? $gejala->bobot
                ?? 0.0
            );

            $optimisticCfUser = 1.0;

            $cfCombine = $this->combineCf(
                $cfCombine,
                $optimisticCfUser * $bobotPakar
            );
        }

        return $cfCombine;
    }

    /**
     * Ambil gejala belum dijawab dari daftar aturan.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Gejala> $gejalaList
     * @param array $answeredCodes
     * @return \Illuminate\Support\Collection<int, \App\Models\Gejala>
     */
    private function getUnansweredSymptoms($gejalaList, array $answeredCodes)
    {
        return $gejalaList->filter(fn ($g) => !in_array($g->kode, $answeredCodes));
    }

    /**
     * Combine two CF values using standard MYCIN combination.
     */
    private function combineCf(float $cf_combine, float $cf_weighted): float
    {
        if ($cf_combine >= 0 && $cf_weighted >= 0) {
            return $cf_combine + ($cf_weighted * (1 - $cf_combine));
        }

        if ($cf_combine < 0 && $cf_weighted < 0) {
            return $cf_combine + ($cf_weighted * (1 + $cf_combine));
        }

        $divisor = 1 - min(abs($cf_combine), abs($cf_weighted));

        return $divisor != 0 ? ($cf_combine + $cf_weighted) / $divisor : $cf_combine;
    }

    /**
     * Simpan hasil konsultasi ke dalam basis data.
     */
    public function saveResult($userId, array $result, array $allAnswers)
    {
        $konsultasi = Konsultasi::create([
            'user_id' => $userId,
            'diagnosa_id' => $result['diagnosa']->id,
            'cf_final' => $result['cf'],
            'tracing' => $result['tracing'],
        ]);

        $gejalaIds = [];

        foreach ($allAnswers as $kode => $ans) {
            if ($this->getCfUser($ans) > 0) {
                $gejala = Gejala::where('kode', $kode)->first();

                if ($gejala) {
                    $gejalaIds[] = $gejala->id;
                }
            }
        }

        $konsultasi->gejala()->attach($gejalaIds);

        return $konsultasi;
    }

    /**
     * EXPLANATION FACILITY (Sistem Penjelasan Ilmiah & Natural)
     * Mengurai proses inferensi Backward Chaining + analisis 3 Dimensi MBI (Maslach Burnout Inventory).
     */
    public function generateExplanation(array $tracing, $diagnosa, float $cfFinal): array
    {
        $explanation = [
            'summary'            => '',
            'reasoning_chain'    => [],
            'dominant_symptoms'  => [],
            'confidence_label'   => '',
            'mbi_analysis'       => [
                'ee_score' => 0.0,
                'dp_score' => 0.0,
                'pa_score' => 0.0,
                'ee_label' => 'Rendah',
                'dp_label' => 'Rendah',
                'pa_label' => 'Tinggi (Kondisi Baik)',
            ],
        ];

        $pct = round($cfFinal * 100, 1);

        if ($cfFinal >= 0.8) {
            $explanation['confidence_label'] = 'Sangat Yakin';
        } elseif ($cfFinal >= 0.6) {
            $explanation['confidence_label'] = 'Cukup Yakin';
        } elseif ($cfFinal >= 0.4) {
            $explanation['confidence_label'] = 'Cukup Mungkin';
        } elseif ($cfFinal >= 0.2) {
            $explanation['confidence_label'] = 'Kemungkinan Rendah';
        } else {
            $explanation['confidence_label'] = 'Tidak Terkonfirmasi';
        }

        $explanation['reasoning_chain'][] = 'Sistem memicu mekanisme Backward Chaining untuk menguji hipotesis burnout secara terstruktur berdasarkan aturan pakar yang aktif.';

        if (isset($tracing['rule_kode'])) {
            $explanation['reasoning_chain'][] = "Mengevaluasi Aturan Pakar {$tracing['rule_kode']} yang berasosiasi dengan diagnosis \"{$diagnosa->nama}\".";

            if (isset($tracing['prioritas'])) {
                $explanation['reasoning_chain'][] = "Aturan {$tracing['rule_kode']} memiliki tingkat prioritas pakar {$tracing['prioritas']} dalam basis pengetahuan.";
            }

            if (isset($tracing['conflict_strategy'])) {
                $strategyLabel = str_replace('_', ' ', $tracing['conflict_strategy']);
                $explanation['reasoning_chain'][] = "Sistem menggunakan metode resolusi konflik \"" . ucwords($strategyLabel) . "\" untuk memvalidasi aturan paling tepat.";
            }

            if (isset($tracing['min_threshold'])) {
                $explanation['reasoning_chain'][] = 'Aturan ini memiliki ambang batas minimum (min_threshold) sebesar ' . number_format((float) $tracing['min_threshold'], 2) . '.';
            }

            if (isset($tracing['cf_combine_gejala']) && isset($tracing['cf_pakar_rule'])) {
                $cfCombine = number_format($tracing['cf_combine_gejala'], 4);
                $cfPakar = number_format($tracing['cf_pakar_rule'], 2);
                $explanation['reasoning_chain'][] = "Kombinasi jawaban gejala menghasilkan skor Certainty Factor (CF) gabungan senilai {$cfCombine}.";
                $explanation['reasoning_chain'][] = 'Skor gabungan dikalikan dengan faktor kepercayaan pakar untuk aturan ini (' . $cfPakar . ') menghasilkan CF Final = ' . number_format($cfFinal, 4) . " ({$pct}%).";
            }

            if (isset($tracing['all_candidate_rules']) && count($tracing['all_candidate_rules']) > 1) {
                $explanation['reasoning_chain'][] = 'Terdapat ' . count($tracing['all_candidate_rules']) . " kandidat aturan yang terpicu secara bersamaan. Sistem memilih aturan {$tracing['rule_kode']} sebagai jalur inferensi terbaik.";
            }
        } elseif (isset($tracing['message'])) {
            $explanation['reasoning_chain'][] = 'Inferensi Fallback: ' . $tracing['message'];
        }

        if (isset($tracing['gejala_details'])) {
            $details = collect($tracing['gejala_details']);

            $activeDetails = $details->filter(fn ($detail) => ($detail['cf_user'] ?? 0) > 0);

            $eeSum = 0.0;
            $eeCount = 0;
            $dpSum = 0.0;
            $dpCount = 0;
            $paSum = 0.0;
            $paCount = 0;

            foreach ($details as $detail) {
                $kategori = $detail['kategori'] ?? 'emosional';
                $cfValue = (float) ($detail['cf_user'] ?? 0.0);

                if ($kategori === 'emosional') {
                    $eeSum += $cfValue;
                    $eeCount++;
                } elseif ($kategori === 'perilaku') {
                    $dpSum += $cfValue;
                    $dpCount++;
                } elseif ($kategori === 'kognitif') {
                    $paSum += $cfValue;
                    $paCount++;
                }
            }

            $eeAvg = $eeCount > 0 ? max(0.0, $eeSum / $eeCount) : 0.0;
            $dpAvg = $dpCount > 0 ? max(0.0, $dpSum / $dpCount) : 0.0;
            $paAvg = $paCount > 0 ? max(0.0, $paSum / $paCount) : 0.0;

            $explanation['mbi_analysis']['ee_score'] = round($eeAvg * 100, 1);
            $explanation['mbi_analysis']['dp_score'] = round($dpAvg * 100, 1);
            $explanation['mbi_analysis']['pa_score'] = round($paAvg * 100, 1);

            $explanation['mbi_analysis']['ee_label'] = $eeAvg >= 0.7 ? 'Tinggi' : ($eeAvg >= 0.4 ? 'Sedang' : 'Rendah');
            $explanation['mbi_analysis']['dp_label'] = $dpAvg >= 0.7 ? 'Tinggi' : ($dpAvg >= 0.4 ? 'Sedang' : 'Rendah');
            $explanation['mbi_analysis']['pa_label'] = $paAvg >= 0.7 ? 'Tinggi (Sangat Terganggu)' : ($paAvg >= 0.4 ? 'Sedang' : 'Rendah (Normal)');

            $sorted = $activeDetails->sortByDesc('cf_sub')->values();

            foreach ($sorted->take(3) as $detail) {
                $explanation['dominant_symptoms'][] = [
                    'nama' => $detail['gejala'],
                    'kode' => $detail['kode'],
                    'kategori' => $detail['kategori'] ?? 'emosional',
                    'impact' => round(($detail['cf_sub'] ?? 0) * 100, 1),
                    'jawaban' => $detail['user_ans'],
                    'evidence_direction' => $detail['evidence_direction'] ?? 'PRESENT_SUPPORTS',
                ];
            }
        }

        $symptomCount = count($explanation['dominant_symptoms']);
        $isHealthyDiagnosis = ($diagnosa->kode ?? null) === 'D01'
            || str_contains(strtolower($diagnosa->nama ?? ''), 'tidak burnout');

        if ($isHealthyDiagnosis) {
            $explanation['summary'] = "Berdasarkan analisis backward chaining pakar, Anda berada pada kondisi **{$diagnosa->nama}** dengan tingkat keyakinan **{$pct}% ({$explanation['confidence_label']})**. "
                . 'Pola jawaban Anda tidak menunjukkan bukti gejala burnout yang dominan, sehingga sistem menilai kondisi Anda cenderung stabil.';
        } elseif ($symptomCount > 0) {
            $topSymptom = $explanation['dominant_symptoms'][0]['nama'];
            $eeLabel = $explanation['mbi_analysis']['ee_label'];
            $dpLabel = $explanation['mbi_analysis']['dp_label'];

            $explanation['summary'] = "Berdasarkan analisis backward chaining pakar, Anda terindikasi berada pada fase **{$diagnosa->nama}** dengan tingkat keyakinan **{$pct}% ({$explanation['confidence_label']})**. "
                . "Dari dimensi Maslach Burnout Inventory (MBI), Anda mengalami tingkat *Kelelahan Emosional (EE)* yang **{$eeLabel}** serta tingkat *Depersonalisasi (DP)* yang **{$dpLabel}**. "
                . "Faktor pemicu utama yang berkontribusi paling besar terhadap kondisi ini adalah gejala **\"{$topSymptom}\"** yang Anda rasakan dengan intensitas \"{$explanation['dominant_symptoms'][0]['jawaban']}\".";
        } else {
            $explanation['summary'] = "Berdasarkan analisis penelusuran pakar, Anda berada pada kondisi **{$diagnosa->nama}** dengan kepastian sebesar **{$pct}%**. "
                . 'Seluruh dimensi evaluasi Maslach Burnout Inventory (MBI) Anda berada dalam batas normal dan stabil. Tidak ditemukan pola gejala kelelahan emosional atau sinisme kerja yang signifikan.';
        }

        return $explanation;
    }
}
