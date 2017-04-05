<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReader\Exception;

use OutOfBoundsException;

final class KeyNotFoundException extends OutOfBoundsException implements ExceptionInterface
{
    public static function fromNonExistentKey(string $key, string ...$parentKeys) : self
    {
        return new self(sprintf(
            'Could not find key "%s" in tree "%s"',
            $key,
            implode('->', $parentKeys)
        ));
    }
}
