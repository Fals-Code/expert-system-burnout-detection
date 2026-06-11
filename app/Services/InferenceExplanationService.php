<?php

namespace App\Services;

use App\Models\InferenceSession;

class InferenceExplanationService
{
    public function build(InferenceSession $session): array
    {
        $session->loadMissing(['traces', 'answers.item']);
        $conclusion = $session->conclusion ?? 'FEASIBILITY_EXHAUSTED';

        return [
            'title' => 'Hasil Inferensi Backward Chaining Berbasis Indikator CBI',
            'status' => $session->status,
            'conclusion_code' => $conclusion,
            'conclusion_label' => (string) config(
                "expert_system.goal_labels.{$conclusion}",
                $conclusion
            ),
            'summary' => $this->summary($session),
            'trace' => $session->traces
                ->map(fn ($trace): array => [
                    'sequence' => $trace->sequence,
                    'event' => $trace->event,
                    'goal' => $trace->goal,
                    'rule_code' => $trace->rule_code,
                    'premise_key' => $trace->premise_key,
                    'result' => $trace->result,
                    'message' => $trace->message,
                    'context' => $trace->context ?? [],
                ])
                ->values()
                ->all(),
            'answers' => $session->answers
                ->sortBy(fn ($answer): int => $answer->item?->position ?? 999)
                ->map(fn ($answer): array => [
                    'code' => $answer->item?->code,
                    'question' => $answer->item?->prompt_text,
                    'answer_key' => $answer->answer_key,
                    'answer_label' => data_get(
                        config('cbi.response_options'),
                        "{$answer->answer_key}.label",
                        $answer->answer_key
                    ),
                    'boolean_value' => (bool) $answer->boolean_value,
                ])
                ->values()
                ->all(),
            'disclaimer' => (string) config('expert_system.disclaimer'),
        ];
    }

    private function summary(InferenceSession $session): string
    {
        if ($session->status === InferenceSession::STATUS_PROVEN) {
            return 'Engine menemukan satu goal yang dapat dibuktikan melalui rule aktif dan fakta jawaban pengguna.';
        }

        if ($session->status === InferenceSession::STATUS_EXHAUSTED) {
            return 'Seluruh goal kandidat telah diuji, tetapi tidak ada rule yang dapat dibuktikan secara lengkap dari fakta yang tersedia.';
        }

        return 'Wawancara adaptif masih berlangsung dan engine masih membutuhkan fakta tambahan.';
    }
}
