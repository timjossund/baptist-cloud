<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * The outcome of a free view check: whether to show the item, and what is left.
 */
final class MeterResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly ?int $remaining,
        public readonly CarbonInterface $resetsOn,
    ) {}
}
