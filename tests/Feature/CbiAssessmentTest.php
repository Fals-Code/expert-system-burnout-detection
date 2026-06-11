<?php

namespace Tests\Feature;

use App\Models\CbiAssessment;
use App\Models\CbiItem;
use App\Models\User;
use Database\Seeders\CbiInstrumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbiAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CbiInstrumentSeeder::class);
    }

    public function test_complete_submission_stores_three_dimension_averages(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $responses = [];

        CbiItem::query()->orderBy('position')->get()->each(
            function (CbiItem $item) use (&$responses): void {
                $responses[$item->code] = match ($item->dimension) {
                    CbiItem::DIMENSION_PERSONAL => 'ALWAYS',
                    CbiItem::DIMENSION_WORK => 'OFTEN',
                    CbiItem::DIMENSION_CLIENT => 'SOMETIMES',
                };
            }
        );

        $this->actingAs($user)
            ->post(route('karyawan.deteksi.next'), ['responses' => $responses])
            ->assertRedirect();

        $assessment = CbiAssessment::query()->firstOrFail();

        $this->assertSame(CbiAssessment::STATUS_COMPLETE, $assessment->status);
        $this->assertSame(19, $assessment->responses_count);
        $this->assertSame(100.0, $assessment->personal_score);
        $this->assertSame(67.86, $assessment->work_score);
        $this->assertSame(50.0, $assessment->client_score);
        $this->assertDatabaseCount('cbi_responses', 19);
        $this->assertDatabaseHas('cbi_responses', [
            'answer_key' => 'OFTEN',
            'raw_score' => 75,
            'normalized_score' => 25,
        ]);
    }

    public function test_submission_with_eighteen_items_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $responses = CbiItem::query()
            ->orderBy('position')
            ->limit(18)
            ->pluck('code')
            ->mapWithKeys(fn (string $code): array => [$code => 'SOMETIMES'])
            ->all();

        $this->actingAs($user)
            ->from(route('karyawan.deteksi'))
            ->post(route('karyawan.deteksi.next'), ['responses' => $responses])
            ->assertRedirect(route('karyawan.deteksi'))
            ->assertSessionHasErrors('responses');

        $this->assertDatabaseCount('cbi_assessments', 0);
    }
}
