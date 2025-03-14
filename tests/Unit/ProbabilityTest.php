<?php

declare(strict_types=1);
// tests/ProbabilityTest.php

use Sphamster\Support\Probability;

it('returns the correct category', function (): void {
    $probability = new Probability('chinese', -2.0);
    expect($probability->category())->toBe('chinese');
});

it('returns the correct log probability', function (): void {
    $logValue = -3.5;
    $probability = new Probability('japanese', $logValue);
    expect($probability->log())->toBe($logValue);
});

it('converts log probability to decimal correctly', function (): void {
    $logValue = -1.0;
    $probability = new Probability('test', $logValue);
    $expectedDecimal = exp($logValue);
    expect($probability->decimal())->toBe($expectedDecimal);
});
