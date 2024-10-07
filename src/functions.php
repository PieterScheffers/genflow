<?php

namespace pisc\GenFlow;

/**
 * Get an attribute of an array or object.
 */
function getAttribute(string|callable $getter, mixed $value, mixed $key): mixed
{
    if (is_callable($getter)) {
        return $getter($value, $key);
    }

    $value = null;

    if (is_object($value) && isset($value->{$getter})) {
        $value = $value->{$getter};
    } elseif (is_object($value) && method_exists($value, $getter)) {
        $value = $value->{$getter}();
    } elseif (is_array($value) && isset($value[$getter])) {
        $value = $value[$getter];
    }

    return $value;
}

/**
 * Short method a new GenFlow instance.
 */
function gen(iterable $iterable): GenFlow
{
    return new GenFlow($iterable);
}
