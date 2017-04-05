<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReader;

use DASPRiD\TreeReader\Exception\KeyNotFoundException;
use DASPRiD\TreeReader\Exception\UnexpectedTypeException;

final class TreeReader
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string[]
     */
    private $parentKeys;

    public function __construct(array $data, string ...$parentKeys)
    {
        $this->data = $data;
        $this->parentKeys = $parentKeys;
    }

    public function hasKey(string $key) : bool
    {
        return array_key_exists($key, $this->data);
    }

    public function hasNonNullValue(string $key) : bool
    {
        return array_key_exists($key, $this->data) && null !== $this->data[$key];
    }

    public function getString(string $key, string $default = null) : string
    {
        return $this->getValue($key, 'string', $default);
    }

    public function getInt(string $key, int $default = null) : int
    {
        return $this->getValue($key, 'integer', $default);
    }

    public function getFloat(string $key, float $default = null) : float
    {
        return $this->getValue($key, 'double', $default);
    }

    public function getBool(string $key, bool $default = null) : bool
    {
        return $this->getValue($key, 'boolean', $default);
    }

    public function getChildren(string $key, array $default = null) : self
    {
        return new TreeReader($this->getValue($key, 'array', $default), ...$this->parentKeys + [$key]);
    }

    private function getValue(string $key, string $expectedType, $default)
    {
        if (!array_key_exists($key, $this->data)) {
            if (null !== $default) {
                return $default;
            }

            throw KeyNotFoundException::fromNonExistentKey($key, ...$this->parentKeys);
        }

        $value = $this->data[$key];

        if (null === $value && null !== $default) {
            return $default;
        }

        $valueType = gettype($value);

        if ($valueType !== $expectedType) {
            throw UnexpectedTypeException::fromUnexpectedType($key, $valueType, $expectedType, ...$this->parentKeys);
        }

        return $value;
    }
}
