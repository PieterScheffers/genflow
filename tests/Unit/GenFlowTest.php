<?php

declare(strict_types=1);

use Pisc\GenFlow\GenFlow;
use Generator;

describe('GenFlow->__construct', function () {
    it('should create a GenFlow instance of an array', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow)->toBeInstanceOf(GenFlow::class);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    });

    it('should create a GenFlow instance of a Generator', function () {
        function createGenerator(): Generator
        {
            for ($i = 1; $i <= 5; $i++) {
                yield $i;
            }
        }

        $genFlow = new GenFlow(createGenerator());

        expect($genFlow)->toBeInstanceOf(GenFlow::class);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    });
});

describe('GenFlow->get', function () {
    it('should return the underlying generator', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);
        $generator = $genFlow->get();

        expect($generator)->toBeInstanceOf(Generator::class);
        expect(iterator_to_array($generator))->toEqual([1, 2, 3, 4, 5]);
    });
});

describe('GenFlow->toArray', function () {
    it('should return the items of the generator as array', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);
        $array = $genFlow->toArray();

        expect($array)->toBeArray();
        expect($array)->toEqual([1, 2, 3, 4, 5]);
    });

    it('should still return all items after multiple toArray calls', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    })->skip();

    it('should preserve the keys', function () {
        $genFlow = new GenFlow([10 => 1, 24 => 2, 36 => 3, 44 => 4, 48 => 5]);
        $array = $genFlow->toArray();

        expect($array)->toBeArray();
        expect($array)->toEqual([10 => 1, 24 => 2, 36 => 3, 44 => 4, 48 => 5]);
    });

    it('should be able to not preserve the keys', function () {
        $genFlow = new GenFlow([10 => 1, 24 => 2, 36 => 3, 44 => 4, 48 => 5]);
        $array = $genFlow->toArray(preserveKeys: false);

        expect($array)->toBeArray();
        expect($array)->toEqual([1, 2, 3, 4, 5]);
    });
});

describe('GenFlow->map', function () {
    it('should map the items of the generator to other items', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        $genFlow = $genFlow->map(function ($item) {
            return $item * 2;
        });

        expect($genFlow->toArray())->toEqual([2, 4, 6, 8, 10]);
    });
});

describe('GenFlow->filter', function () {
    it('should filter the items of the generator', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        $genFlow = $genFlow->filter(function ($item) {
            return $item * 2 !== 4;
        });

        expect($genFlow->toArray())->toEqual([0 => 1, 2 => 3, 3 => 4, 4 => 5]);
    });
});

describe('GenFlow->count', function () {
    it('should count the items of the generator', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->count())->toEqual(5);
    });
});

describe('GenFlow->batch', function () {
    it('should batch the items of the generator', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $batches = $genFlow->batch(3)->toArray();

        expect($batches)->toHaveCount(4);
        expect($batches[0])->toEqual([1, 2, 3]);
        expect($batches[1])->toEqual([3 => 4, 4 => 5, 5 => 6]);
        expect($batches[2])->toEqual([6 => 7, 7 => 8, 8 => 9]);
        expect($batches[3])->toEqual([9 => 10]);
    });
});

describe('GenFlow->first', function () {
    it('should return the first element of the generator', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->first())->toEqual(1);
    });

    it('should return null if the generator is empty', function () {
        $genFlow = new GenFlow([]);

        expect($genFlow->first())->toBeNull();
    });

    it('should still return the whole generator after a first call', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    });

    it('should return the same element if called multiple times', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
        expect($genFlow->first())->toEqual(1);
    });
});

describe('GenFlow->isEmpty', function () {
    it('should return true if the generator is empty', function () {
        $genFlow = new GenFlow([]);

        expect($genFlow->isEmpty())->toBeTrue();
    });

    it('should return false if the generator is not empty', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->isEmpty())->toBeFalse();
    });

    it('should still return the whole generator after a isEmpty call', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow->isEmpty())->toBeFalse();
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    });
});

describe('GenFlow->groupBy', function () {
    it('should group the items by a key of an object in the generator', function () {
        $genFlow = new GenFlow([
            (object) ['id' => 1, 'name' => 'John', 'type' => 'manager'],
            (object) ['id' => 3, 'name' => 'Jane', 'type' => 'manager'],
            (object) ['id' => 4, 'name' => 'Joe', 'type' => 'employee'],
            (object) ['id' => 6, 'name' => 'Jill', 'type' => 'employee'],
            (object) ['id' => 9, 'name' => 'Jack', 'type' => 'employee'],
        ]);

        $groupedBy = $genFlow->groupBy('type');

        expect($groupedBy)->toHaveCount(2);
        expect($groupedBy['manager'])->toEqual([
            (object) ['id' => 1, 'name' => 'John', 'type' => 'manager'],
            (object) ['id' => 3, 'name' => 'Jane', 'type' => 'manager'],
        ]);
        expect($groupedBy['employee'])->toEqual([
            (object) ['id' => 4, 'name' => 'Joe', 'type' => 'employee'],
            (object) ['id' => 6, 'name' => 'Jill', 'type' => 'employee'],
            (object) ['id' => 9, 'name' => 'Jack', 'type' => 'employee'],
        ]);
    });
});

describe('GenFlow->indexBy', function () {
    it('should indexBy the items of the generator', function () {
        $genFlow = new GenFlow([
            (object) ['id' => 10, 'name' => 'John'],
            (object) ['id' => 12, 'name' => 'Jane'],
            (object) ['id' => 13, 'name' => 'Joe'],
            (object) ['id' => 24, 'name' => 'Jill'],
            (object) ['id' => 35, 'name' => 'Jack'],
        ]);

        $indexedBy = $genFlow->indexBy('id')->toArray();

        expect($indexedBy)->toEqual([
            10 => (object) ['id' => 10, 'name' => 'John'],
            12 => (object) ['id' => 12, 'name' => 'Jane'],
            13 => (object) ['id' => 13, 'name' => 'Joe'],
            24 => (object) ['id' => 24, 'name' => 'Jill'],
            35 => (object) ['id' => 35, 'name' => 'Jack'],
        ]);
    });
});
