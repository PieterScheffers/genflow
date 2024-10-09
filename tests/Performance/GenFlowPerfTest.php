<?php

declare(strict_types=1);

use Pisc\GenFlow\GenFlow;

test('performance', function () {
    function createBigGenerator(): Generator
    {
        for ($i = 0; $i < 1000000; $i++) {
            yield ['id' => $i, 'name' => 'John', 'age' => $i];
        }
    }

    $memoryBefore = memory_get_usage();

    $genFlow = new GenFlow(createBigGenerator());
    $result = $genFlow
        ->map(fn ($item) => array_merge($item, ['age' => $item['id'] % 100]))
        ->filter(fn ($item) => $item['age'] > 50)
        ->reduce(fn ($acc, $item) => $acc + $item['age'], 0);

    $memoryAfter = memory_get_usage();

    expect($result)->toEqual(36750000);

    $megaByte = 1024 * 1024;
    expect($memoryAfter - $memoryBefore)->toBeLessThan($megaByte);

    // $array = iterator_to_array(createBigGenerator()); // PHP Fatal error:  Allowed memory size of xxxxxxxxx bytes exhausted
    // var_dump(memory_get_peak_usage());
});
