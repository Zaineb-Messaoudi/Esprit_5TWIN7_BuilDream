<?php

use App\Services\AlternativeDatesFinder;

function findAlternativeDates(string $start, string $end, array $busy, string $today = '2026-12-01', int $maxShift = 30, int $limit = 3): array
{
    return (new AlternativeDatesFinder)->find($start, $end, $busy, $today, $maxShift, $limit);
}

it('says the equipment is available when the period is free', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [['start' => '2026-12-01', 'end' => '2026-12-10']]);

    expect($result['status'])->toBe('available')
        ->and($result['duration_days'])->toBe(4)
        ->and($result['alternatives'])->toBe([]);
});

it('proposes the nearest free periods before and after', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [['start' => '2026-12-15', 'end' => '2026-12-18']]);

    expect($result['status'])->toBe('alternatives')
        ->and($result['duration_days'])->toBe(4)
        ->and($result['alternatives'][0])->toBe(['start' => '2026-12-19', 'end' => '2026-12-22', 'shift_days' => 4, 'direction' => 'later'])
        ->and($result['alternatives'][1])->toBe(['start' => '2026-12-11', 'end' => '2026-12-14', 'shift_days' => 4, 'direction' => 'earlier'])
        ->and($result['alternatives'][2]['start'])->toBe('2026-12-23')
        ->and($result['alternatives'][2]['end'])->toBe('2026-12-26');
});

it('never proposes periods in the past', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [['start' => '2026-12-15', 'end' => '2026-12-18']], '2026-12-12');

    expect(array_column($result['alternatives'], 'start'))->toBe(['2026-12-19', '2026-12-23', '2026-12-27'])
        ->and(array_unique(array_column($result['alternatives'], 'direction')))->toBe(['later']);
});

it('finds a gap between two busy periods', function () {
    $result = findAlternativeDates('2026-12-12', '2026-12-14', [
        ['start' => '2026-12-10', 'end' => '2026-12-14'],
        ['start' => '2026-12-18', 'end' => '2026-12-25'],
    ]);

    expect($result['alternatives'][0])->toBe(['start' => '2026-12-15', 'end' => '2026-12-17', 'shift_days' => 3, 'direction' => 'later'])
        ->and($result['alternatives'][1])->toBe(['start' => '2026-12-07', 'end' => '2026-12-09', 'shift_days' => 5, 'direction' => 'earlier'])
        ->and($result['alternatives'][2])->toBe(['start' => '2026-12-04', 'end' => '2026-12-06', 'shift_days' => 8, 'direction' => 'earlier']);
});

it('treats both ends of a period as busy days', function () {
    $busy = [['start' => '2026-12-15', 'end' => '2026-12-18']];

    expect(findAlternativeDates('2026-12-19', '2026-12-20', $busy)['status'])->toBe('available')
        ->and(findAlternativeDates('2026-12-18', '2026-12-20', $busy)['status'])->toBe('alternatives');
});

it('returns none when nothing is free inside the search window', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [['start' => '2026-12-15', 'end' => '2026-12-18']], '2026-12-01', 2);

    expect($result['status'])->toBe('none')
        ->and($result['alternatives'])->toBe([]);
});

it('rejects a period that ends before it starts', function () {
    $result = findAlternativeDates('2026-12-18', '2026-12-15', []);

    expect($result['status'])->toBe('invalid');
});

it('ignores malformed busy periods', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [
        [],
        ['start' => '2026-12-15'],
        ['start' => '2026-12-18', 'end' => '2026-12-15'],
    ]);

    expect($result['status'])->toBe('available');
});

it('respects the maximum number of proposals', function () {
    $result = findAlternativeDates('2026-12-15', '2026-12-18', [['start' => '2026-12-15', 'end' => '2026-12-18']], '2026-12-01', 30, 1);

    expect($result['alternatives'])->toHaveCount(1)
        ->and($result['alternatives'][0]['start'])->toBe('2026-12-19');
});
