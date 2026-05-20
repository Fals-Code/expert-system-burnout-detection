<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ExpertSystemService
{
    // Default threshold fallback: 0.25
    /**
     * Certainty Factor Mapping (CF_User)
     * Mengubah jawaban linguistik menjadi nilai kepastian kuantitatif
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
     * Backward Chaining & Certainty Factor Engine dengan Conflict Resolution, Priority, dan Rule Validation
     * 
     * @param array $answers ['G01' => 'Sering', ...]
     * @param string $conflictStrategy 'highest_cf' | 'first_matched' | 'priority_based'
     * @return array [hasil_diagnosa, cf_final, tracing]
     */
    public function solve(array $answers, string $conflictStrategy = 'highest_cf'): array
    {
        $conflictStrategy = func_num_args() > 1 ? func_get_arg(1) : 'highest_cf';
        $serializedRules = \Illuminate\Support\Facades\Cache::remember('aturan_active_rules_base64', 86400, function () {
            return base64_encode(serialize(Aturan::where('is_active', true)
                ->with(['diagnosa', 'gejala'])
                ->get()));
        });
        $rules = unserialize(base64_decode($serializedRules));

        // 2. Jika tidak ada aturan sama sekali, kembalikan hasil default (Rendah)
        if ($rules->isEmpty()) {
            return $this->defaultResult('Tidak ada aturan aktif di basis pengetahuan.', $answers);
        }

        $triggeredRules = [];

        // 3. Evaluasi setiap aturan menggunakan kombinasi Certainty Factor
        foreach ($rules as $rule) {
            $gejalaList = $rule->gejala;
            if ($gejalaList->isEmpty()) continue;

            $cf_combine = 0.0;
            $rule_trace = [];
            $answered_count = 0;

            foreach ($gejalaList as $gejala) {
                $user_answer = 'Belum Dijawab';
                $cf_user = 0.0;
                if (isset($answers[$gejala->kode])) {
                    $user_answer = $answers[$gejala->kode];
                    $cf_user = $this->getCfUser($user_answer);
                }

                // Gunakan bobot_pakar dari pivot aturan_gejala, fallback ke bobot gejala umum
                $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                
                // CF[H,E] = CF_user * CF_pakar (bobot gejala)
                $cf_weighted = $cf_user * $bobot_pakar;

                if (isset($answers[$gejala->kode])) {
                    $answered_count++;
                }

                // Kalkulasi CF Combine dengan rumus standar MYCIN (mendukung CF negatif)
                if ($cf_combine >= 0 && $cf_weighted >= 0) {
                    $cf_combine = $cf_combine + ($cf_weighted * (1 - $cf_combine));
                } elseif ($cf_combine < 0 && $cf_weighted < 0) {
                    $cf_combine = $cf_combine + ($cf_weighted * (1 + $cf_combine));
                } else {
                    // Berbeda tanda
                    $divisor = 1 - min(abs($cf_combine), abs($cf_weighted));
                    if ($divisor != 0) {
                        $cf_combine = ($cf_combine + $cf_weighted) / $divisor;
                    }
                }

                $rule_trace[] = [
                    'gejala' => $gejala->nama,
                    'kode' => $gejala->kode,
                    'kategori' => $gejala->kategori,
                    'user_ans' => $user_answer,
                    'cf_user' => $cf_user,
                    'bobot' => $bobot_pakar,
                    'cf_sub' => $cf_weighted
                ];
            }

            // CF_final = CF_combine_gejala * CF_pakar_rule
            $cf_final_rule = $cf_combine * $rule->cf_pakar;

            // ── VALIDASI RULE (RULE VALIDATION) ──
            // Aturan hanya dianggap "Triggered" jika CF Final memenuhi ambang batas minimum aturan (min_threshold)
            $is_valid = $cf_final_rule >= $rule->min_threshold;

            if ($is_valid && $answered_count > 0) {
                $triggeredRules[] = [
                    'rule' => $rule,
                    'cf_final' => $cf_final_rule,
                    'cf_combine' => $cf_combine,
                    'trace_details' => $rule_trace,
                    'prioritas' => $rule->prioritas,
                    'diagnosa' => $rule->diagnosa
                ];
            }
        }

        // 4. Jika tidak ada aturan yang terpicu sama sekali
        if (empty($triggeredRules)) {
            return $this->defaultResult('Tidak ada gejala yang cukup signifikan untuk memicu aturan deteksi.', $answers);
        }

        // ── RESOLUSI KONFLIK (CONFLICT RESOLUTION) ──
        // Mengatasi kondisi jika ada beberapa aturan yang terpicu untuk diagnosis yang berbeda
        switch ($conflictStrategy) {
            case 'priority_based':
                // Urutkan berdasarkan prioritas aturan tertinggi (angka prioritas terbesar)
                // Jika prioritas sama, pilih CF tertinggi
                usort($triggeredRules, function ($a, $b) {
                    if ($b['prioritas'] === $a['prioritas']) {
                        return $b['cf_final'] <=> $a['cf_final'];
                    }
                    return $b['prioritas'] <=> $a['prioritas'];
                });
                break;

            case 'first_matched':
                // Menggunakan aturan pertama berdasarkan tingkat keparahan diagnosis (D01 -> D04)
                usort($triggeredRules, function ($a, $b) {
                    return $a['diagnosa']->kode <=> $b['diagnosa']->kode;
                });
                break;

            case 'highest_cf':
            default:
                // Urutkan murni berdasarkan CF tertinggi untuk akurasi sains paling optimal
                usort($triggeredRules, function ($a, $b) {
                    return $b['cf_final'] <=> $a['cf_final'];
                });
                break;
        }

        // Pilih aturan pemenang setelah resolusi konflik
        $winner = $triggeredRules[0];

        $final_result = [
            'diagnosa' => $winner['diagnosa'],
            'cf' => $winner['cf_final'],
            'tracing' => [
                'rule_kode' => $winner['rule']->kode,
                'cf_pakar_rule' => $winner['rule']->cf_pakar,
                'cf_combine_gejala' => $winner['cf_combine'],
                'gejala_details' => $winner['trace_details'],
                'prioritas' => $winner['prioritas'],
                'conflict_strategy' => $conflictStrategy,
                'method' => 'Backward Chaining (CF Combine) with ' . ucfirst(str_replace('_', ' ', $conflictStrategy)) . ' Conflict Resolution',
                'all_candidate_rules' => collect($triggeredRules)->map(fn($r) => [
                    'rule_kode' => $r['rule']->kode,
                    'diagnosa' => $r['diagnosa']->nama,
                    'cf_final' => round($r['cf_final'], 4),
                    'prioritas' => $r['prioritas']
                ])->toArray()
            ]
        ];

        return $final_result;
    }

    /**
     * Membantu pencarian hasil default saat tidak ada hipotesis terpenuhi
     */
    protected function defaultResult(string $reason, array $answers = []): array
    {
        $serializedDefault = \Illuminate\Support\Facades\Cache::remember('diagnosa_default_rendah_base64', 86400, function () {
            $diagnosa = Diagnosa::where('tingkat', 'RENDAH')->first();
            return $diagnosa ? base64_encode(serialize($diagnosa)) : null;
        });
        $defaultDiagnosa = $serializedDefault ? unserialize(base64_decode($serializedDefault)) : null;
        $defaultDiagnosa = $defaultDiagnosa ?? new Diagnosa([
            'id' => 4,
            'kode' => 'D04',
            'nama' => 'Risiko Burnout Rendah (Normal/Mild)',
            'tingkat' => 'RENDAH',
            'deskripsi' => 'Kondisi psikologis Anda cenderung stabil.',
            'saran' => 'Pertahankan rutinitas positif Anda.'
        ]);

        // Build gejala_details from ALL user answers so the results page can display them
        $gejalaDetails = [];
        if (!empty($answers)) {
            $answeredKodes = array_keys($answers);
            $gejalaList = Gejala::whereIn('kode', $answeredKodes)->get()->keyBy('kode');
            foreach ($answers as $kode => $userAnswer) {
                $gejala = $gejalaList[$kode] ?? null;
                if ($gejala) {
                    $cfUser = $this->getCfUser($userAnswer);
                    $gejalaDetails[] = [
                        'gejala' => $gejala->nama,
                        'kode' => $gejala->kode,
                        'kategori' => $gejala->kategori ?? 'emosional',
                        'user_ans' => $userAnswer,
                        'cf_user' => $cfUser,
                        'bobot' => $gejala->bobot ?? 0.5,
                        'cf_sub' => $cfUser * ($gejala->bobot ?? 0.5),
                    ];
                }
            }
        }

        return [
            'diagnosa' => $defaultDiagnosa,
            'cf' => 0.0,
            'tracing' => [
                'message' => $reason,
                'method' => 'Fallback Default Result',
                'gejala_details' => $gejalaDetails,
            ]
        ];
    }

    /**
     * Empathetic symptom phrasing dictionary translating clinical names into highly compassionate questions
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
        $serializedDiagnosas = \Illuminate\Support\Facades\Cache::remember('diagnosa_ordered_base64', 86400, function () {
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
        $serializedRules = \Illuminate\Support\Facades\Cache::remember("aturan_by_diagnosa_{$diagnosa->id}_base64", 86400, function () use ($diagnosa) {
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
            $maxPossibleCf = $optimisticCfCombine * $rule->cf_pakar;

            if ($maxPossibleCf < $rule->min_threshold) {
                continue;
            }

            $unanswered = $this->getUnansweredSymptoms($gejalaList, $answeredCodes);
            if ($unanswered->isEmpty()) {
                continue;
            }

            foreach ($unanswered as $gejala) {
                $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                $potentialCfCombine = $this->combineCf($currentCfCombine, 1.0 * $bobot_pakar);
                $estimatedGain = max(0.0, ($potentialCfCombine * $rule->cf_pakar) - ($currentCfCombine * $rule->cf_pakar));

                $candidateSymptoms[$gejala->kode] = $this->mergeSymptomCandidate(
                    $candidateSymptoms[$gejala->kode] ?? [],
                    $gejala,
                    $estimatedGain,
                    $bobot_pakar,
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
     * Hitung kombinasi CF saat ini berdasarkan gejala yang sudah dijawab.
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

            $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
            $cfCombine = $this->combineCf($cfCombine, $this->getCfUser($answers[$gejala->kode]) * $bobot_pakar);
        }

        return $cfCombine;
    }

    /**
     * Hitung kombinasi CF optimistik untuk gejala yang belum dijawab.
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

            $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
            $cfCombine = $this->combineCf($cfCombine, 1.0 * $bobot_pakar);
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
        return $gejalaList->filter(fn ($g) => !in_array($g->kode, $answeredCodes))ch ($unanswered as $gejala) {
                $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                $potentialCfCombine = $this->combineCf($currentCfCombine, 1.0 * $bobot_pakar);
                $estimatedGain = max(0.0, ($potentialCfCombine * $rule->cf_pakar) - ($currentCfCombine * $rule->cf_pakar));

                $candidateSymptoms[$gejala->kode] = $this->mergeSymptomCandidate(
                    $candidateSymptoms[$gejala->kode] ?? [],
                    $gejala,
                    $estimatedGain,
                    $bobot_pakar,
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
     * Hitung kombinasi CF saat ini berdasarkan gejala yang sudah dijawab.
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

            $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
            $cfCombine = $this->combineCf($cfCombine, $this->getCfUser($answers[$gejala->kode]) * $bobot_pakar);
        }

        return $cfCombine;
    }

    /**
     * Hitung kombinasi CF optimistik untuk gejala yang belum dijawab.
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

            $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
            $cfCombine = $this->combineCf($cfCombine, 1.0 * $bobot_pakar);
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
     * Simpan hasil konsultasi ke dalam basis data
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
            // Simpan gejala jika tingkat keyakinan > 0
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
     * Mengurai proses inferensi Backward Chaining + analisis 3 Dimensi MBI (Maslach Burnout Inventory)
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
                'pa_label' => 'Tinggi (Kondisi Baik)'
            ]
        ];

        // 1. Tentukan Confidence Label (Tingkat Keyakinan)
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

        // 2. Susun Rantai Inferensi (Reasoning Chain)
        $explanation['reasoning_chain'][] = 'Sistem memicu mekanisme Backward Chaining untuk menguji hipotesis burnout secara terstruktur dari tingkat paling berat (Severe) hingga paling ringan (Normal).';

        if (isset($tracing['rule_kode'])) {
            $explanation['reasoning_chain'][] = "Mengevaluasi Aturan Pakar {$tracing['rule_kode']} yang berasosiasi dengan diagnosis \"{$diagnosa->nama}\".";
            
            if (isset($tracing['prioritas'])) {
                $explanation['reasoning_chain'][] = "Aturan {$tracing['rule_kode']} memiliki tingkat prioritas pakar {$tracing['prioritas']} dalam basis pengetahuan.";
            }

            if (isset($tracing['conflict_strategy'])) {
                $strategyLabel = str_replace('_', ' ', $tracing['conflict_strategy']);
                $explanation['reasoning_chain'][] = "Sistem menggunakan metode resolusi konflik \"" . ucwords($strategyLabel) . "\" untuk memvalidasi aturan paling tepat.";
            }

            if (isset($tracing['cf_combine_gejala']) && isset($tracing['cf_pakar_rule'])) {
                $cfCombine = number_format($tracing['cf_combine_gejala'], 4);
                $cfPakar = number_format($tracing['cf_pakar_rule'], 2);
                $explanation['reasoning_chain'][] = "Kombinasi jawaban gejala Anda menghasilkan skor Certainty Factor (CF) gabungan senilai {$cfCombine}.";
                $explanation['reasoning_chain'][] = "Skor gabungan dikalikan dengan faktor kepercayaan pakar untuk aturan ini ({$cfPakar}) menghasilkan CF Final = " . number_format($cfFinal, 4) . " ({$pct}%).";
            }

            if (isset($tracing['all_candidate_rules']) && count($tracing['all_candidate_rules']) > 1) {
                $explanation['reasoning_chain'][] = "Terdapat " . count($tracing['all_candidate_rules']) . " kandidat aturan yang terpicu secara bersamaan. Sistem berhasil meresolusi konflik dan memilih aturan {$tracing['rule_kode']} sebagai jalur inferensi terbaik.";
            }
        } elseif (isset($tracing['message'])) {
            $explanation['reasoning_chain'][] = "Inferensi Fallback: " . $tracing['message'];
        }

        // 3. Ekstrak Gejala Dominan & Analisis Dimensi MBI (Maslach Burnout Inventory)
        if (isset($tracing['gejala_details'])) {
            $details = collect($tracing['gejala_details']);

            // Filter gejala yang dirasakan oleh user
            $activeDetails = $details->filter(fn($d) => $d['cf_user'] > 0);

            // Klasifikasi berdasarkan kategori MBI
            $ee_sum = 0.0; $ee_count = 0;
            $dp_sum = 0.0; $dp_count = 0;
            $pa_sum = 0.0; $pa_count = 0;

            foreach ($details as $detail) {
                $kategori = $detail['kategori'] ?? 'emosional';
                $cf_val = $detail['cf_user'];

                if ($kategori === 'emosional') {
                    $ee_sum += $cf_val;
                    $ee_count++;
                } elseif ($kategori === 'perilaku') {
                    // Depersonalisasi
                    $dp_sum += $cf_val;
                    $dp_count++;
                } elseif ($kategori === 'kognitif') {
                    // Reduced Personal Accomplishment (Dalam MBI dibalik, semakin tinggi keluhan kognitif = pencapaian rendah)
                    $pa_sum += $cf_val;
                    $pa_count++;
                }
            }

            // Hitung rata-rata skor dimensi dan pastikan tidak negatif (clamp ke 0)
            $ee_avg = $ee_count > 0 ? max(0.0, $ee_sum / $ee_count) : 0.0;
            $dp_avg = $dp_count > 0 ? max(0.0, $dp_sum / $dp_count) : 0.0;
            $pa_avg = $pa_count > 0 ? max(0.0, $pa_sum / $pa_count) : 0.0;

            $explanation['mbi_analysis']['ee_score'] = round($ee_avg * 100, 1);
            $explanation['mbi_analysis']['dp_score'] = round($dp_avg * 100, 1);
            $explanation['mbi_analysis']['pa_score'] = round($pa_avg * 100, 1);

            // Beri label ilmiah MBI
            $explanation['mbi_analysis']['ee_label'] = $ee_avg >= 0.7 ? 'Tinggi' : ($ee_avg >= 0.4 ? 'Sedang' : 'Rendah');
            $explanation['mbi_analysis']['dp_label'] = $dp_avg >= 0.7 ? 'Tinggi' : ($dp_avg >= 0.4 ? 'Sedang' : 'Rendah');
            $explanation['mbi_analysis']['pa_label'] = $pa_avg >= 0.7 ? 'Tinggi (Sangat Terganggu)' : ($pa_avg >= 0.4 ? 'Sedang' : 'Rendah (Normal)');

            // Susun Gejala Dominan (3 kontributor terbesar)
            $sorted = $activeDetails->sortByDesc('cf_sub')->values();
            foreach ($sorted->take(3) as $detail) {
                $explanation['dominant_symptoms'][] = [
                    'nama'    => $detail['gejala'],
                    'kode'    => $detail['kode'],
                    'kategori' => $detail['kategori'] ?? 'emosional',
                    'impact'  => round($detail['cf_sub'] * 100, 1),
                    'jawaban' => $detail['user_ans'],
                ];
            }
        }

        // 4. Susun Narasi Ringkas (Summary) Secara Natural
        $symptomCount = count($explanation['dominant_symptoms']);
        if ($symptomCount > 0) {
            $topSymptom = $explanation['dominant_symptoms'][0]['nama'];
            $eeLabel = $explanation['mbi_analysis']['ee_label'];
            $dpLabel = $explanation['mbi_analysis']['dp_label'];
            
            $explanation['summary'] = "Berdasarkan analisis backward chaining pakar, Anda terindikasi berada pada fase **{$diagnosa->nama}** dengan tingkat keyakinan **{$pct}% ({$explanation['confidence_label']})**. "
                . "Dari dimensi Maslach Burnout Inventory (MBI), Anda mengalami tingkat *Kelelahan Emosional (EE)* yang **{$eeLabel}** serta tingkat *Depersonalisasi (DP)* yang **{$dpLabel}**. "
                . "Faktor pemicu utama yang berkontribusi paling besar terhadap kondisi ini adalah gejala **\"{$topSymptom}\"** yang Anda rasakan dengan intensitas \"{$explanation['dominant_symptoms'][0]['jawaban']}\".";
        } else {
            $explanation['summary'] = "Berdasarkan analisis penelusuran pakar, Anda berada pada kondisi **{$diagnosa->nama}** dengan kepastian sebesar **{$pct}%**. "
                . "Seluruh dimensi evaluasi Maslach Burnout Inventory (MBI) Anda berada dalam batas normal dan stabil. Tidak ditemukan pola gejala kelelahan emosional atau sinisme kerja yang signifikan.";
        }

        return $explanation;
    }
}
