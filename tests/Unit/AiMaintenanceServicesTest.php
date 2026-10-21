<?php

namespace Tests\Unit;

use App\Services\Ai\FakeAiClient;
use App\Services\Maintenance\DamageSeverityClassifier;
use App\Services\Maintenance\RuleBasedFailureRiskService;
use Tests\TestCase;

class AiMaintenanceServicesTest extends TestCase
{
    public function test_failure_risk_rules_and_boundaries_are_coherent(): void
    {
        $service = new RuleBasedFailureRiskService;

        $this->assertSame('low', $service->predict(0, 0, 0)['level']);
        $this->assertSame('medium', $service->predict(10, 1, 100)['level']);
        $this->assertSame('high', $service->predict(20, 4, null)['level']);
        $this->assertSame(100, $service->predict(999, 999, 9999)['score']);
        $this->assertSame(0, $service->predict(-1, -1, 0)['score']);
    }

    public function test_severity_accepts_valid_ai_json_for_all_levels(): void
    {
        foreach ([['minor', 2], ['moderate', 6], ['severe', 20]] as [$severity, $hours]) {
            $client = new FakeAiClient(json_encode(['severity' => $severity, 'repair_hours' => $hours]));
            $result = (new DamageSeverityClassifier($client))->classify('Description');
            $this->assertSame($severity, $result['severity']);
            $this->assertSame('ai', $result['source']);
        }
    }

    public function test_invalid_ai_output_uses_keywords_and_empty_comment_is_safe(): void
    {
        $classifier = new DamageSeverityClassifier(new FakeAiClient('invalid'));

        $this->assertSame('severe', $classifier->classify('Câble cassé, risque incendie')['severity']);
        $this->assertSame('moderate', $classifier->classify('Panne avec fissure')['severity']);
        $this->assertSame('minor', $classifier->classify('Petite rayure')['severity']);
        $this->assertSame(['severity' => 'minor', 'repair_hours' => 0, 'source' => 'fallback'], $classifier->classify(''));
    }
}
