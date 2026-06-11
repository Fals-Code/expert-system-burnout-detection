<?php

namespace App\Services;

use App\ExpertSystem\InferenceResult;
use App\Models\CbiItem;
use App\Models\InferenceAnswer;
use App\Models\InferenceSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdaptiveInterviewService
{
    public function __construct(
        private readonly InferenceEngineService $engine
    ) {
    }

    public function getOrCreateSession(User $user): InferenceSession
    {
        $existing = InferenceSession::query()
            ->where('user_id', $user->id)
            ->where('status', InferenceSession::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $goals = array_values(
            config('expert_system.goal_order', [])
        );

        if ($goals === []) {
            throw new InvalidArgumentException(
                'Backward chaining goal order is not configured.'
            );
        }

        return InferenceSession::query()->create([
            'user_id' => $user->id,
            'root_goal' => $goals[0],
            'current_goal' => $goals[0],
            'goal_queue' => $goals,
            'goal_index' => 0,
            'status' => InferenceSession::STATUS_IN_PROGRESS,
        ]);
    }

    public function advance(InferenceSession $session): InferenceResult
    {
        $this->engine->begin($session);

        $goals = array_values($session->goal_queue ?? []);
        $goalIndex = max(0, (int) $session->goal_index);

        while ($goalIndex < count($goals)) {
            $goal = (string) $goals[$goalIndex];

            $session->update([
                'current_goal' => $goal,
                'goal_index' => $goalIndex,
                'current_question_code' => null,
            ]);

            $result = $this->engine->solve($session, $goal);

            if ($result->needsQuestion()) {
                $session->update([
                    'current_question_code' => $result->question?->code,
                ]);

                return $result;
            }

            if ($result->isProven()) {
                $session->update([
                    'status' => InferenceSession::STATUS_PROVEN,
                    'conclusion' => $goal,
                    'current_question_code' => null,
                    'completed_at' => now(),
                ]);

                return $result;
            }

            $goalIndex++;
            $session->update(['goal_index' => $goalIndex]);
        }

        $session->update([
            'status' => InferenceSession::STATUS_EXHAUSTED,
            'conclusion' => 'FEASIBILITY_EXHAUSTED',
            'current_goal' => null,
            'current_question_code' => null,
            'completed_at' => now(),
        ]);

        return InferenceResult::exhausted('FEASIBILITY_EXHAUSTED');
    }

    public function recordAnswer(
        InferenceSession $session,
        CbiItem $item,
        string $answerKey
    ): InferenceAnswer {
        if ($session->status !== InferenceSession::STATUS_IN_PROGRESS) {
            throw new InvalidArgumentException(
                'Cannot answer a completed inference session.'
            );
        }

        if ($session->current_question_code !== $item->code) {
            throw new InvalidArgumentException(
                'The submitted item is not the fact currently requested by the engine.'
            );
        }

        $answerKey = strtoupper(trim($answerKey));
        $rawScore = data_get(
            config('cbi.response_options'),
            "{$answerKey}.score"
        );

        if ($rawScore === null) {
            throw new InvalidArgumentException(
                "Unsupported answer key: {$answerKey}."
            );
        }

        $booleanValue = in_array(
            $answerKey,
            config('expert_system.true_answer_keys', []),
            true
        );

        return DB::transaction(function () use (
            $session,
            $item,
            $answerKey,
            $rawScore,
            $booleanValue
        ): InferenceAnswer {
            $answer = InferenceAnswer::query()->updateOrCreate(
                [
                    'session_id' => $session->id,
                    'cbi_item_id' => $item->id,
                ],
                [
                    'answer_key' => $answerKey,
                    'raw_score' => (int) $rawScore,
                    'boolean_value' => $booleanValue,
                ]
            );

            $session->update(['current_question_code' => null]);

            return $answer;
        });
    }
}
