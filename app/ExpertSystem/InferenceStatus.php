<?php

namespace App\ExpertSystem;

enum InferenceStatus: string
{
    case NEED_FACT = 'NEED_FACT';
    case PROVEN = 'PROVEN';
    case REJECTED = 'REJECTED';
    case EXHAUSTED = 'EXHAUSTED';
    case LOOP_DETECTED = 'LOOP_DETECTED';
}
