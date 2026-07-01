<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Diagnosa;
use App\Models\Gejala;
use Illuminate\Support\Collection;

class BackwardChainingEngine
{
    public const GOAL_ORDER = ['TINGGI', 'SEDANG', 'RENDAH'];

    public function __construct(private readonly CertaintyFactorCalculator $calculator) {}

    /**
     * Prove burnout goals from highest to lowest. Stop on the first confirmed goal.
     *
     * @param  array<string, string>  $answers
     * @return array{diagnosa: Diagnosa, cf: float, tracing: array}
     */
    public function solve(array $answers): array
    {
        $evaluatedGoals = [];

        foreach (self::GOAL_ORDER as $goalLevel) {
            $diagnosa = $this->diagnosisForGoal($goalLevel);

            if (! $diagnosa) {
                $evaluatedGoals[] = [
                    'goal' => $goalLevel,
                    'status' => 'missing_diagnosis',
                ];

                continue;
            }

            $rules = $this->rulesForDiagnosis($diagnosa);
            $goalTrace = [
                'goal' => $goalLevel,
                'diagnosa_kode' => $diagnosa->kode,
                'diagnosa' => $diagnosa->nama,
                'rules' => [],
            ];

            $bestRule = null;

            foreach ($rules as $rule) {
                $evaluation = $this->evaluateRule($rule, $answers);
                $goalTrace['rules'][] = $evaluation;

                if (! $evaluation['accepted']) {
                    continue;
                }

                if ($bestRule === null || $evaluation['cf_rule'] > $bestRule['cf_rule']) {
                    $bestRule = $evaluation;
                }
            }

            if ($bestRule !== null) {
                $evaluatedGoals[] = array_merge($goalTrace, ['status' => 'confirmed']);

                return [
                    'diagnosa' => $diagnosa,
                    'cf' => $bestRule['cf_rule'],
                    'tracing' => [
                        'method' => 'Backward Chaining Goal-Driven + Certainty Factor',
                        'goal_order' => self::GOAL_ORDER,
                        'goal_terkonfirmasi' => $goalLevel,
                        'rule_kode' => $bestRule['rule_kode'],
                        'cf_average_premis' => $bestRule['cf_average_premis'],
                        'cf_pakar_rule' => $bestRule['cf_pakar_rule'],
                        'cf_rule' => $bestRule['cf_rule'],
                        'cf_combine_gejala' => $bestRule['cf_average_premis'],
                        'min_threshold' => $bestRule['min_threshold'],
                        'gejala_details' => $bestRule['gejala_details'],
                        'evaluated_goals' => $evaluatedGoals,
                        'all_candidate_rules' => collect($goalTrace['rules'])->map(fn ($rule) => [
                            'rule_kode' => $rule['rule_kode'],
                            'cf_final' => $rule['cf_rule'],
                            'accepted' => $rule['accepted'],
                            'min_threshold' => $rule['min_threshold'],
                        ])->values()->all(),
                    ],
                ];
            }

            $evaluatedGoals[] = array_merge($goalTrace, ['status' => 'rejected']);
        }

        return $this->fallbackResult($answers, $evaluatedGoals);
    }

    /**
     * Return exactly one unanswered symptom code from the current goal stack.
     *
     * @param  array<string, string>  $answers
     */
    public function nextSymptom(array $answers): ?string
    {
        foreach (self::GOAL_ORDER as $goalLevel) {
            $diagnosa = $this->diagnosisForGoal($goalLevel);

            if (! $diagnosa) {
                continue;
            }

            foreach ($this->rulesForDiagnosis($diagnosa) as $rule) {
                $evaluation = $this->evaluateRule($rule, $answers);

                if ($evaluation['accepted']) {
                    return null;
                }

                if ($evaluation['max_possible_cf'] < $evaluation['min_threshold']) {
                    continue;
                }

                $unanswered = collect($evaluation['gejala_details'])
                    ->filter(fn ($detail) => $detail['user_ans'] === 'Belum Dijawab')
                    ->sortByDesc('bobot')
                    ->first();

                if ($unanswered) {
                    return $unanswered['kode'];
                }
            }
        }

        return null;
    }

    public function evaluateRule(Aturan $rule, array $answers): array
    {
        $premiseCfs = [];
        $optimisticPremiseCfs = [];
        $gejalaDetails = [];

        foreach ($rule->gejala as $gejala) {
            $answer = $answers[$gejala->kode] ?? null;
            $weight = (float) ($gejala->bobot ?? $gejala->pivot->bobot_pakar ?? 0.0);
            $userCf = $this->calculator->userCf($answer);
            $premiseCf = $this->calculator->premiseCf($answer, $weight);

            $premiseCfs[] = $premiseCf;
            $optimisticPremiseCfs[] = $answer === null
                ? $this->calculator->premiseCf('Sering', $weight)
                : $premiseCf;

            $gejalaDetails[] = [
                'gejala' => $gejala->nama,
                'kode' => $gejala->kode,
                'kategori' => $gejala->kategori ?? 'emosional',
                'user_ans' => $answer ?? 'Belum Dijawab',
                'raw_cf_user' => $userCf,
                'cf_user' => $userCf,
                'bobot' => $weight,
                'cf_sub' => $premiseCf,
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ];
        }

        $cfPakar = (float) ($rule->cf_pakar ?? 0.0);
        $threshold = (float) ($rule->min_threshold ?? 0.25);
        $cfRule = $this->calculator->ruleCf($premiseCfs, $cfPakar);
        $maxPossibleCf = $this->calculator->ruleCf($optimisticPremiseCfs, $cfPakar);
        $allAnswered = collect($gejalaDetails)->every(fn ($detail) => $detail['user_ans'] !== 'Belum Dijawab');

        return [
            'rule_kode' => $rule->kode,
            'cf_pakar_rule' => $cfPakar,
            'cf_average_premis' => $premiseCfs === [] ? 0.0 : $this->calculator->clamp(array_sum($premiseCfs) / count($premiseCfs)),
            'cf_rule' => $cfRule,
            'max_possible_cf' => $maxPossibleCf,
            'min_threshold' => $threshold,
            'accepted' => $allAnswered && $cfRule >= $threshold,
            'all_answered' => $allAnswered,
            'gejala_details' => $gejalaDetails,
        ];
    }

    private function diagnosisForGoal(string $goalLevel): ?Diagnosa
    {
        return Diagnosa::query()
            ->where('tingkat', $goalLevel)
            ->first();
    }

    /**
     * @return Collection<int, Aturan>
     */
    private function rulesForDiagnosis(Diagnosa $diagnosa): Collection
    {
        return Aturan::query()
            ->where('diagnosa_id', $diagnosa->id)
            ->where('is_active', true)
            ->with(['diagnosa', 'gejala'])
            ->orderByDesc('prioritas')
            ->orderBy('kode')
            ->get();
    }

    private function fallbackResult(array $answers, array $evaluatedGoals): array
    {
        $diagnosa = Diagnosa::query()
            ->where('tingkat', 'TIDAK_TERINDIKASI')
            ->orWhere('kode', 'D01')
            ->first() ?? Diagnosa::make([
                'id' => 1,
                'kode' => 'D01',
                'nama' => 'Tidak Terindikasi Burnout',
                'tingkat' => 'TIDAK_TERINDIKASI',
                'deskripsi' => 'Tidak ada rule burnout yang melewati ambang konfirmasi.',
                'saran' => 'Pertahankan pola kerja sehat dan lakukan check-in berkala.',
            ]);

        $gejala = Gejala::query()
            ->whereIn('kode', array_keys($answers))
            ->get()
            ->keyBy('kode');

        $details = [];

        foreach ($answers as $kode => $answer) {
            $symptom = $gejala[$kode] ?? null;

            if (! $symptom) {
                continue;
            }

            $details[] = [
                'gejala' => $symptom->nama,
                'kode' => $symptom->kode,
                'kategori' => $symptom->kategori ?? 'emosional',
                'user_ans' => $answer,
                'raw_cf_user' => $this->calculator->userCf($answer),
                'cf_user' => $this->calculator->userCf($answer),
                'bobot' => (float) $symptom->bobot,
                'cf_sub' => $this->calculator->premiseCf($answer, (float) $symptom->bobot),
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ];
        }

        return [
            'diagnosa' => $diagnosa,
            'cf' => 0.0,
            'tracing' => [
                'method' => 'Backward Chaining Goal-Driven + Certainty Factor',
                'goal_order' => self::GOAL_ORDER,
                'goal_terkonfirmasi' => 'TIDAK_TERINDIKASI',
                'message' => 'Tidak ada goal burnout yang mencapai threshold.',
                'rule_kode' => 'FALLBACK',
                'cf_average_premis' => 0.0,
                'cf_pakar_rule' => 0.0,
                'cf_rule' => 0.0,
                'min_threshold' => 0.25,
                'gejala_details' => $details,
                'evaluated_goals' => $evaluatedGoals,
            ],
        ];
    }
}
