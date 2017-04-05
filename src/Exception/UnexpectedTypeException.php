<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReader\Exception;

use UnexpectedValueException;

final class UnexpectedTypeException extends UnexpectedValueException implements ExceptionInterface
{
    public static function fromUnexpectedType(
        string $key,
        string $valueType,
        string $expectedType,
        string ...$parentKeys
    ) : self {
        return new self(sprintf(
            'Value of key "%s" in tree "%s" is of type %s, but %s was expected',
            $key,
            implode('->', $parentKeys),
            $valueType,
            $expectedType
        ));
    }
}
