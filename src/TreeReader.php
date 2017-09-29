<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReader;

use DASPRiD\TreeReader\Exception\KeyNotFoundException;
use IteratorAggregate;
use Traversable;

final class TreeReader implements IteratorAggregate
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string[]
     */
    private $parentKeys;

    public function __construct(array $data, string $name = 'root', string ...$parentKeys)
    {
        $this->data = $data;
        $this->parentKeys = array_merge($parentKeys, [$name]);
    }

    /**
     * @return Traversable|Item[]
     */
    public function getIterator() : Traversable
    {
        foreach ($this->data as $key => $value) {
            yield new Item((string) $key, $value);
        }
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
        return $this->getItem($key, $default)->getString();
    }

    public function getInt(string $key, int $default = null) : int
    {
        return $this->getItem($key, $default)->getInt();
    }

    public function getFloat(string $key, float $default = null) : float
    {
        return $this->getItem($key, $default)->getFloat();
    }

    public function getBool(string $key, bool $default = null) : bool
    {
        return $this->getItem($key, $default)->getBool();
    }

    public function getChildren(string $key, array $default = null) : self
    {
        return $this->getItem($key, $default)->getChildren();
    }

    public function getArray(string $key, array $default = null) : array
    {
        return $this->getItem($key, $default)->getArray();
    }

    private function getItem(string $key, $default)
    {
        if ($this->hasNonNullValue($key)) {
            return new Item($key, $this->data[$key], ...$this->parentKeys);
        }

        if (null !== $default) {
            return new Item($key, $default, ...$this->parentKeys);
        }

        throw KeyNotFoundException::fromNonExistentKey($key, ...$this->parentKeys);
    }
}
