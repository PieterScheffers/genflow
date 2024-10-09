# GenFlow

Library to easily map, filter and reduce on a Generator.

## Install

```bash
composer require pisc/genflow
```

## Usage

```php
<?php

namespace MyNamespace;

use function Pisc/GenFlow/gen;

function myGenerator()
{
    yield 1;
    yield 2;
    yield 3;
}

gen(myGenerator())
    ->map(fn ($item) => $item * 2)
    ->filter(fn ($item) => $item < 5)
    ->toArray(); // [2, 4]
```
