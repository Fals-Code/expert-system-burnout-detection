<?php

namespace App\ExpertSystem;

use App\Models\CbiItem;

final readonly class InferenceResult
{
    public function __construct(
        public InferenceStatus $status,
        public string $goal,
        public ?CbiItem $question = null,
        public ?string $ruleCode = null,
        public array $context = []
    ) {
    }

    public static function needFact(
        string $goal,
        CbiItem $question,
        ?string $ruleCode = null,
        array $context = []
    ): self {
        return new self(
            InferenceStatus::NEED_FACT,
            $goal,
            $question,
            $ruleCode,
            $context
        );
    }

    public static function proven(
        string $goal,
        ?string $ruleCode = null,
        array $context = []
    ): self {
        return new self(
            InferenceStatus::PROVEN,
            $goal,
            null,
            $ruleCode,
            $context
        );
    }

    public static function rejected(
        string $goal,
        ?string $ruleCode = null,
        array $context = []
    ): self {
        return new self(
            InferenceStatus::REJECTED,
            $goal,
            null,
            $ruleCode,
            $context
        );
    }

    public static function exhausted(
        string $goal,
        array $context = []
    ): self {
        return new self(
            InferenceStatus::EXHAUSTED,
            $goal,
            null,
            null,
            $context
        );
    }

    public static function loopDetected(
        string $goal,
        array $context = []
    ): self {
        return new self(
            InferenceStatus::LOOP_DETECTED,
            $goal,
            null,
            null,
            $context
        );
    }

    public function needsQuestion(): bool
    {
        return $this->status === InferenceStatus::NEED_FACT;
    }

    public function isProven(): bool
    {
        return $this->status === InferenceStatus::PROVEN;
    }
}
