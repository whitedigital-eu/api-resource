<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource;

use function array_is_list;
use function is_array;
use function ltrim;

use const PHP_INT_MAX;

final class Functions
{
    public function isAssociative(mixed $array): bool
    {
        if (!is_array(value: $array) || [] === $array) {
            return false;
        }

        return !array_is_list(array: $array);
    }

    public function makeOneDimension(array $array, string $base = '', string $separator = '.', bool $onlyLast = false, int $depth = 0, int $maxDepth = PHP_INT_MAX, array $result = []): array
    {
        if ($depth <= $maxDepth) {
            foreach ($array as $key => $value) {
                $key = ltrim(string: $base . '.' . $key, characters: '.');

                if ((new self())->isAssociative(array: $value)) {
                    $result = $this->makeOneDimension(array: $value, base: $key, separator: $separator, onlyLast: $onlyLast, depth: $depth + 1, maxDepth: $maxDepth, result: $result);

                    if ($onlyLast) {
                        continue;
                    }
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }
}
