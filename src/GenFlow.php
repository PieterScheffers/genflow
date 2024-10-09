<?php

declare(strict_types=1);

namespace Pisc\GenFlow;

use Generator;
use JsonSerializable;

/**
 * @template TItem
 */
class GenFlow implements JsonSerializable
{
    /**
     * @var Generator<TItem> $generator
     */
    protected Generator $generator;

    /**
     * @param iterable<TItem> $iterable
     */
    final public function __construct(iterable $iterable)
    {
        $this->generator = static::iterableToGenerator($iterable);
    }

    /**
     * Create a Generator from an array.
     *
     * @template S
     *
     * @param iterable<S> $iterable
     *
     * @return Generator<S>
     */
    public static function iterableToGenerator(iterable $iterable): Generator
    {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @param iterable<TItem> $iterable
     */
    public static function from(iterable $iterable): static
    {
        return new static($iterable);
    }

    /**
     * @return Generator<TItem>
     */
    public function get(): Generator
    {
        return $this->generator;
    }

    /**
     * @return TItem[]
     */
    public function toArray(bool $preserveKeys = true): array
    {
        return iterator_to_array($this->generator, $preserveKeys);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param mixed[]|null $args
     */
    public function apply(callable $fn, array|null $args = null): int
    {
        return iterator_apply($this->generator, $fn, $args);
    }

    public static function mapGenerator(Generator $generator, string|callable $attribute): Generator
    {
        foreach ($generator as $key => $value) {
            $newValue = getAttribute($attribute, $value, $key);
            yield $key => $newValue;
        }
    }

    public function map(string|callable $attribute): static
    {
        return new static(static::mapGenerator($this->generator, $attribute));
    }

    public static function filterGenerator(Generator $generator, string|callable $attribute): Generator
    {
        foreach ($generator as $key => $value) {
            if (getAttribute($attribute, $value, $key)) {
                yield $key => $value;
            }
        }
    }

    public function filter(string|callable $attribute): static
    {
        return new static(static::filterGenerator($this->generator, $attribute));
    }

    /**
     * @template S
     *
     * @param S $initial
     * @return S
     */
    public function reduce(callable $fn, mixed $initial = null): mixed
    {
        $acc = $initial;

        foreach ($this->generator as $key => $value) {
            $acc = $fn($acc, $value, $key);
        }

        return $acc;
    }

    public function count(): int
    {
        return iterator_count($this->generator);
    }

    /**
     * Batch a generator into chunks.
     *
     * @template S
     *
     * @param Generator<S> $generator
     *
     * @return Generator<S[]>
     */
    public static function batchGenerator(Generator $generator, int $batchSize = 100): Generator
    {
        $batch = [];

        foreach ($generator as $key => $value) {
            $batch[$key] = $value;

            if (count($batch) >= $batchSize) {
                yield $batch;
                $batch = [];
            }
        }

        if (!empty($batch)) {
            yield $batch;
            $batch = [];
        }
    }

    public function batch(int $batchSize = 100): static
    {
        return new static(static::batchGenerator($this->generator, $batchSize));
    }

    /**
     * @return TItem|null
     */
    public function first(): mixed
    {
        foreach ($this->generator as $value) {
            return $value;
        }

        return null;
    }

    public function isEmpty(): bool
    {
        $first = $this->first();
        return $first === null;
    }

    /**
     * @return array<string, TItem[]>
     */
    public function groupBy(string|callable $attribute): array
    {
        return $this->reduce(function ($acc, $value, $key) use ($attribute): array {
            $key = getAttribute($attribute, $value, $key);
            $acc[$key][] = $value;

            return $acc;
        }, []);
    }

    /**
     * Batch a generator into chunks.
     *
     * @template S
     *
     * @param Generator<S> $generator
     *
     * @return Generator<S>
     */
    public function indexGeneratorBy(Generator $generator, string|callable $attribute): Generator
    {
        foreach ($generator as $key => $value) {
            $newKey = getAttribute($attribute, $value, $key);
            yield $newKey => $value;
        }
    }

    public function indexBy(string|callable $attribute): static
    {
        return new static(static::indexGeneratorBy($this->generator, $attribute));
    }
}
