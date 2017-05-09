<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReader;

use DASPRiD\TreeReader\Exception\KeyNotFoundException;
use DASPRiD\TreeReader\Exception\UnexpectedTypeException;
use Traversable;

final class Item
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string[]
     */
    private $parentKeys;

    public function __construct(string $key, $value, string ...$parentKeys)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parentKeys = $parentKeys;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function hasNonNullValue() : bool
    {
        return null !== $this->value;
    }

    public function getString(string $default = null) : string
    {
        return $this->getValue('string', $default);
    }

    public function getInt(int $default = null) : int
    {
        return $this->getValue('integer', $default);
    }

    public function getFloat(float $default = null) : float
    {
        return $this->getValue('double', $default);
    }

    public function getBool(bool $default = null) : bool
    {
        return $this->getValue('boolean', $default);
    }

    public function getChildren(array $default = null) : TreeReader
    {
        return new TreeReader($this->getValue('array', $default), $this->key, ...$this->parentKeys);
    }

    private function getValue(string $expectedType, $default)
    {
        if (null === $this->value && null !== $default) {
            return $default;
        }

        $valueType = gettype($this->value);

        if ($valueType !== $expectedType) {
            throw UnexpectedTypeException::fromUnexpectedType(
                $this->key,
                $valueType,
                $expectedType,
                ...$this->parentKeys
            );
        }

        return $this->value;
    }
}
