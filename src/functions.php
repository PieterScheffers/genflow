<?php

declare(strict_types=1);

namespace Pisc\GenFlow;

/**
 * Get an attribute of an array or object.
 */
function getAttribute(string|callable $getter, mixed $objectOrArray, mixed $key): mixed
{
    if (is_callable($getter)) {
        return $getter($objectOrArray, $key);
    }

    $value = null;

    if (is_object($objectOrArray) && isset($objectOrArray->{$getter})) {
        $value = $objectOrArray->{$getter};
    } elseif (is_object($objectOrArray) && method_exists($objectOrArray, $getter)) {
        $value = $objectOrArray->{$getter}();
    } elseif (is_array($objectOrArray) && isset($objectOrArray[$getter])) {
        $value = $objectOrArray[$getter];
    }

    return $value;
}

/**
 * Short method to create a new GenFlow instance.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 * @return GenFlow<T>
 */
function gen(iterable $iterable): GenFlow
{
    return new GenFlow($iterable);
}
