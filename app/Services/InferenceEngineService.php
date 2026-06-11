<?php

namespace App\Services;

use App\ExpertSystem\InferenceResult;
use App\ExpertSystem\InferenceStatus;
use App\Models\ExpertPremise;
use App\Models\ExpertRule;
use App\Models\InferenceAnswer;
use App\Models\InferenceSession;
use Illuminate\Support\Collection;

class InferenceEngineService
{
    private int $traceSequence = 0;

    /** @var array<string, InferenceResult> */
    private array $memo = [];

    /** @var Collection<int, InferenceAnswer> */
    private Collection $answers;

    public function begin(InferenceSession $session): void
    {
        $session->traces()->delete();
        $this->traceSequence = 0;
        $this->memo = [];
        $this->answers = $session->answers()
            ->with('item')
            ->get()
            ->keyBy('cbi_item_id');
    }

    public function solve(
        InferenceSession $session,
        string $goal,
        array $visitedGoals = []
    ): InferenceResult {
        if (in_array($goal, $visitedGoals, true)) {
            $this->trace(
                $session,
                event: 'LOOP_DETECTED',
                goal: $goal,
                message: "Loop dihentikan karena goal {$goal} sudah ada pada jalur rekursi.",
                context: ['visited_goals' => $visitedGoals]
            );

            return InferenceResult::loopDetected($goal, [
                'visited_goals' => $visitedGoals,
            ]);
        }

        if (isset($this->memo[$goal])) {
            $cached = $this->memo[$goal];
            $this->trace(
                $session,
                event: 'GOAL_CACHE_HIT',
                goal: $goal,
                result: $cached->isProven(),
                message: "Hasil goal {$goal} digunakan kembali dari evaluasi sebelumnya."
            );

            return $cached;
        }

        $this->trace(
            $session,
            event: 'GOAL_START',
            goal: $goal,
            message: "Sistem mulai menguji hipotesis {$this->label($goal)}."
        );

        $rules = ExpertRule::query()
            ->where('goal', $goal)
            ->where('is_active', true)
            ->with(['premises.cbiItem'])
            ->orderBy('priority')
            ->get();

        if ($rules->isEmpty()) {
            $this->trace(
                $session,
                event: 'GOAL_NO_RULES',
                goal: $goal,
                result: false,
                message: "Tidak ada rule aktif yang dapat membuktikan goal {$goal}."
            );

            return $this->memo[$goal] = InferenceResult::exhausted($goal);
        }

        $visitedGoals[] = $goal;
        $firstPending = null;
        $loopDetected = false;

        foreach ($rules as $rule) {
            $result = $this->evaluateRule(
                $session,
                $rule,
                $visitedGoals
            );

            if ($result->status === InferenceStatus::PROVEN) {
                $this->trace(
                    $session,
                    event: 'GOAL_PROVEN',
                    goal: $goal,
                    ruleCode: $rule->code,
                    result: true,
                    message: "Hipotesis {$this->label($goal)} terbukti melalui rule {$rule->code}."
                );

                return $this->memo[$goal] = InferenceResult::proven(
                    $goal,
                    $rule->code,
                    $result->context
                );
            }

            if ($result->status === InferenceStatus::NEED_FACT
                && $firstPending === null) {
                $firstPending = $result;
            }

            if ($result->status === InferenceStatus::LOOP_DETECTED) {
                $loopDetected = true;
            }
        }

        if ($firstPending instanceof InferenceResult) {
            return $this->memo[$goal] = $firstPending;
        }

        if ($loopDetected) {
            return $this->memo[$goal] = InferenceResult::loopDetected($goal);
        }

        $this->trace(
            $session,
            event: 'GOAL_REJECTED',
            goal: $goal,
            result: false,
            message: "Hipotesis {$this->label($goal)} tidak dapat dibuktikan dari fakta yang tersedia."
        );

        return $this->memo[$goal] = InferenceResult::rejected($goal);
    }

    private function evaluateRule(
        InferenceSession $session,
        ExpertRule $rule,
        array $visitedGoals
    ): InferenceResult {
        $premises = $rule->premises;
        $required = $rule->threshold();
        $total = $premises->count();
        $satisfied = 0;
        $unresolved = 0;
        $firstPending = null;
        $loopDetected = false;

        $this->trace(
            $session,
            event: 'RULE_START',
            goal: $rule->goal,
            ruleCode: $rule->code,
            message: "Rule {$rule->code} dievaluasi dengan operator {$rule->operator}; diperlukan {$required} dari {$total} premis."
        );

        foreach ($premises as $index => $premise) {
            $premiseResult = $this->evaluatePremise(
                $session,
                $premise,
                $visitedGoals,
                $rule
            );

            if ($premiseResult->status === InferenceStatus::PROVEN) {
                $satisfied++;
            } elseif ($premiseResult->status === InferenceStatus::NEED_FACT) {
                $unresolved++;
                $firstPending ??= $premiseResult;
            } elseif ($premiseResult->status === InferenceStatus::LOOP_DETECTED) {
                $unresolved++;
                $loopDetected = true;
            }

            $remaining = $total - ($index + 1);
            $maximumPossible = $satisfied + $unresolved + $remaining;

            if ($satisfied >= $required) {
                $this->trace(
                    $session,
                    event: 'RULE_PROVEN',
                    goal: $rule->goal,
                    ruleCode: $rule->code,
                    result: true,
                    message: "Rule {$rule->code} terbukti: {$satisfied} premis telah memenuhi ambang {$required}.",
                    context: [
                        'satisfied' => $satisfied,
                        'required' => $required,
                        'total' => $total,
                    ]
                );

                return InferenceResult::proven($rule->goal, $rule->code, [
                    'satisfied' => $satisfied,
                    'required' => $required,
                    'total' => $total,
                ]);
            }

            if ($maximumPossible < $required) {
                $this->trace(
                    $session,
                    event: 'RULE_INFEASIBLE',
                    goal: $rule->goal,
                    ruleCode: $rule->code,
                    result: false,
                    message: "Rule {$rule->code} dihentikan: maksimum {$maximumPossible} premis dapat terpenuhi, lebih kecil dari ambang {$required}.",
                    context: [
                        'satisfied' => $satisfied,
                        'maximum_possible' => $maximumPossible,
                        'required' => $required,
                    ]
                );

                return InferenceResult::rejected($rule->goal, $rule->code);
            }
        }

        if ($firstPending instanceof InferenceResult) {
            return $firstPending;
        }

        if ($loopDetected) {
            return InferenceResult::loopDetected($rule->goal);
        }

        $this->trace(
            $session,
            event: 'RULE_REJECTED',
            goal: $rule->goal,
            ruleCode: $rule->code,
            result: false,
            message: "Rule {$rule->code} ditolak karena hanya {$satisfied} dari {$required} premis yang terpenuhi."
        );

        return InferenceResult::rejected($rule->goal, $rule->code);
    }

    private function evaluatePremise(
        InferenceSession $session,
        ExpertPremise $premise,
        array $visitedGoals,
        ExpertRule $rule
    ): InferenceResult {
        if ($premise->premise_type === ExpertPremise::TYPE_GOAL) {
            $subGoalResult = $this->solve(
                $session,
                $premise->premise_key,
                $visitedGoals
            );

            if ($subGoalResult->needsQuestion()
                || $subGoalResult->status === InferenceStatus::LOOP_DETECTED) {
                return $subGoalResult;
            }

            $actual = $subGoalResult->status === InferenceStatus::PROVEN;
            $satisfied = $actual === $premise->expected_boolean;

            $this->trace(
                $session,
                event: 'SUBGOAL_RESULT',
                goal: $rule->goal,
                ruleCode: $rule->code,
                premiseKey: $premise->premise_key,
                result: $satisfied,
                message: sprintf(
                    'Subgoal %s bernilai %s dan %s ekspektasi rule.',
                    $this->label($premise->premise_key),
                    $actual ? 'TRUE' : 'FALSE',
                    $satisfied ? 'memenuhi' : 'tidak memenuhi'
                )
            );

            return $satisfied
                ? InferenceResult::proven($premise->premise_key)
                : InferenceResult::rejected($premise->premise_key);
        }

        $answer = $this->answers->get($premise->cbi_item_id);

        if (! $answer) {
            $question = $premise->cbiItem;

            $this->trace(
                $session,
                event: 'QUESTION_REQUIRED',
                goal: $rule->goal,
                ruleCode: $rule->code,
                premiseKey: $premise->premise_key,
                message: "Fakta {$premise->premise_key} belum diketahui dan harus ditanyakan kepada pengguna."
            );

            return InferenceResult::needFact(
                $rule->goal,
                $question,
                $rule->code,
                ['premise_key' => $premise->premise_key]
            );
        }

        $actual = (bool) $answer->boolean_value;
        $satisfied = $actual === $premise->expected_boolean;
        $answerLabel = data_get(
            config('cbi.response_options'),
            "{$answer->answer_key}.label",
            $answer->answer_key
        );

        $this->trace(
            $session,
            event: 'FACT_EVALUATED',
            goal: $rule->goal,
            ruleCode: $rule->code,
            premiseKey: $premise->premise_key,
            result: $satisfied,
            message: sprintf(
                'Indikator %s bernilai %s karena pengguna menjawab %s; premis %s.',
                $premise->premise_key,
                $actual ? 'TRUE' : 'FALSE',
                $answerLabel,
                $satisfied ? 'terpenuhi' : 'tidak terpenuhi'
            ),
            context: [
                'answer_key' => $answer->answer_key,
                'raw_score' => $answer->raw_score,
                'actual_boolean' => $actual,
                'expected_boolean' => $premise->expected_boolean,
            ]
        );

        return $satisfied
            ? InferenceResult::proven($premise->premise_key)
            : InferenceResult::rejected($premise->premise_key);
    }

    private function trace(
        InferenceSession $session,
        string $event,
        string $message,
        ?string $goal = null,
        ?string $ruleCode = null,
        ?string $premiseKey = null,
        ?bool $result = null,
        array $context = []
    ): void {
        $session->traces()->create([
            'sequence' => ++$this->traceSequence,
            'event' => $event,
            'goal' => $goal,
            'rule_code' => $ruleCode,
            'premise_key' => $premiseKey,
            'result' => $result,
            'message' => $message,
            'context' => $context === [] ? null : $context,
        ]);
    }

    private function label(string $goal): string
    {
        return (string) config(
            "expert_system.goal_labels.{$goal}",
            $goal
        );
    }
}
