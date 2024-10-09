<?php

declare(strict_types=1);

use Pisc\GenFlow\GenFlow;

describe('GenFlow->__construct', function () {
    it('should create a GenFlow instance of an array', function () {
        $genFlow = new GenFlow([1, 2, 3, 4, 5]);

        expect($genFlow)->toBeInstanceOf(GenFlow::class);
        expect($genFlow->toArray())->toEqual([1, 2, 3, 4, 5]);
    });
});
