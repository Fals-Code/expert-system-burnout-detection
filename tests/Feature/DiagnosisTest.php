<?php

namespace Tests\Feature;

use Tests\TestCase;

class DiagnosisTest extends TestCase
{
    public function test_legacy_rule_engine_is_retired(): void
    {
        $this->markTestSkipped('Legacy custom-rule engine has been replaced by dimensional assessment tests.');
    }
}
