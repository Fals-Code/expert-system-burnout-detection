<?php

namespace Tests\Feature;

use App\ExpertSystem\InferenceStatus;
use App\Models\CbiItem;
use App\Models\ExpertPremise;
use App\Models\ExpertRule;
use App\Models\InferenceAnswer;
use App\Models\InferenceSession;
use App\Models\User;
use App\Services\InferenceEngineService;
use Database\Seeders\CbiBackwardChainingRuleSeeder;
use Database\Seeders\CbiInstrumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecursiveBackwardChainingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CbiInstrumentSeeder::class);
        $this->seed(CbiBackwardChainingRuleSeeder::class);
    }

    public function test_engine_requests_only_the_first_unknown_fact(): void
    {
        $session = $this->makeSession();
        $engine = app(InferenceEngineService::class);
        $engine->begin($session);

        $result = $engine->solve(
            $session,
            'BURNOUT_KERJA_KRONIS'
        );

        $this->assertSame(InferenceStatus::NEED_FACT, $result->status);
        $this->assertSame('CBI-PB-01', $result->question?->code);
        $this->assertDatabaseHas('inference_traces', [
            'session_id' => $session->id,
            'event' => 'QUESTION_REQUIRED',
            'premise_key' => 'CBI-PB-01',
        ]);
    }

    public function test_recursive_engine_proves_chronic_work_burnout_goal(): void
    {
        $session = $this->makeSession();

        foreach (['CBI-PB-01', 'CBI-PB-02', 'CBI-PB-03', 'CBI-PB-04'] as $code) {
            $this->answer($session, $code, true);
        }

        foreach (['CBI-PB-05', 'CBI-PB-06'] as $code) {
            $this->answer($session, $code, false);
        }

        foreach (['CBI-WB-01', 'CBI-WB-02', 'CBI-WB-03', 'CBI-WB-04'] as $code) {
            $this->answer($session, $code, true);
        }

        foreach (['CBI-WB-05', 'CBI-WB-06', 'CBI-WB-07'] as $code) {
            $this->answer($session, $code, false);
        }

        $engine = app(InferenceEngineService::class);
        $engine->begin($session);
        $result = $engine->solve(
            $session,
            'BURNOUT_KERJA_KRONIS'
        );

        $this->assertSame(InferenceStatus::PROVEN, $result->status);
        $this->assertSame('R-CHRONIC-ALL', $result->ruleCode);
        $this->assertDatabaseHas('inference_traces', [
            'session_id' => $session->id,
            'event' => 'GOAL_PROVEN',
            'goal' => 'BURNOUT_KERJA_KRONIS',
            'result' => true,
        ]);
    }

    public function test_k_of_n_rule_stops_when_threshold_is_no_longer_feasible(): void
    {
        $session = $this->makeSession();

        foreach (['CBI-PB-01', 'CBI-PB-02', 'CBI-PB-03'] as $code) {
            $this->answer($session, $code, false);
        }

        $engine = app(InferenceEngineService::class);
        $engine->begin($session);
        $result = $engine->solve($session, 'BURNOUT_PERSONAL');

        $this->assertSame(InferenceStatus::REJECTED, $result->status);
        $this->assertDatabaseHas('inference_traces', [
            'session_id' => $session->id,
            'event' => 'RULE_INFEASIBLE',
            'rule_code' => 'R-PERSONAL-4-OF-6',
        ]);
        $this->assertDatabaseMissing('inference_traces', [
            'session_id' => $session->id,
            'event' => 'QUESTION_REQUIRED',
            'premise_key' => 'CBI-PB-04',
        ]);
    }

    public function test_visited_goals_prevent_recursive_rule_loop(): void
    {
        $session = $this->makeSession();

        $ruleA = ExpertRule::query()->create([
            'code' => 'R-LOOP-A',
            'goal' => 'LOOP_A',
            'operator' => ExpertRule::OPERATOR_ALL,
            'required_count' => 1,
            'priority' => 1,
            'is_active' => true,
        ]);
        $ruleA->premises()->create([
            'premise_type' => ExpertPremise::TYPE_GOAL,
            'premise_key' => 'LOOP_B',
            'expected_boolean' => true,
            'sequence' => 1,
        ]);

        $ruleB = ExpertRule::query()->create([
            'code' => 'R-LOOP-B',
            'goal' => 'LOOP_B',
            'operator' => ExpertRule::OPERATOR_ALL,
            'required_count' => 1,
            'priority' => 1,
            'is_active' => true,
        ]);
        $ruleB->premises()->create([
            'premise_type' => ExpertPremise::TYPE_GOAL,
            'premise_key' => 'LOOP_A',
            'expected_boolean' => true,
            'sequence' => 1,
        ]);

        $engine = app(InferenceEngineService::class);
        $engine->begin($session);
        $result = $engine->solve($session, 'LOOP_A');

        $this->assertSame(InferenceStatus::LOOP_DETECTED, $result->status);
        $this->assertDatabaseHas('inference_traces', [
            'session_id' => $session->id,
            'event' => 'LOOP_DETECTED',
            'goal' => 'LOOP_A',
        ]);
    }

    public function test_controller_stores_one_answer_then_asks_the_next_fact(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);

        $this->actingAs($user)
            ->get(route('karyawan.deteksi'))
            ->assertOk()
            ->assertSee('CBI-PB-01');

        $session = InferenceSession::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('karyawan.deteksi.next'), [
                'session_id' => $session->id,
                'item_code' => 'CBI-PB-01',
                'answer_key' => 'OFTEN',
            ])
            ->assertRedirect(route('karyawan.deteksi'));

        $this->assertDatabaseHas('inference_answers', [
            'session_id' => $session->id,
            'answer_key' => 'OFTEN',
            'raw_score' => 75,
            'boolean_value' => true,
        ]);

        $this->actingAs($user)
            ->get(route('karyawan.deteksi'))
            ->assertOk()
            ->assertSee('CBI-PB-02');
    }

    private function makeSession(): InferenceSession
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $goals = config('expert_system.goal_order');

        return InferenceSession::query()->create([
            'user_id' => $user->id,
            'root_goal' => $goals[0],
            'current_goal' => $goals[0],
            'goal_queue' => $goals,
            'goal_index' => 0,
            'status' => InferenceSession::STATUS_IN_PROGRESS,
        ]);
    }

    private function answer(
        InferenceSession $session,
        string $itemCode,
        bool $value
    ): void {
        $item = CbiItem::query()->where('code', $itemCode)->firstOrFail();

        InferenceAnswer::query()->create([
            'session_id' => $session->id,
            'cbi_item_id' => $item->id,
            'answer_key' => $value ? 'OFTEN' : 'SELDOM',
            'raw_score' => $value ? 75 : 25,
            'boolean_value' => $value,
        ]);
    }
}
