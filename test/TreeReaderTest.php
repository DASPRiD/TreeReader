<?php
declare(strict_types = 1);

namespace DASPRiD\TreeReaderTest;

use DASPRiD\TreeReader\Exception\KeyNotFoundException;
use DASPRiD\TreeReader\Exception\UnexpectedTypeException;
use DASPRiD\TreeReader\TreeReader;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TreeReaderTest extends TestCase
{
    public function testHasKey()
    {
        $treeReader = new TreeReader(['foo' => 'bar']);
        $this->assertTrue($treeReader->hasKey('foo'));
        $this->assertFalse($treeReader->hasKey('bar'));
    }

    public function testHasNonNullValue()
    {
        $treeReader = new TreeReader(['foo' => 'bar', 'baz' => null]);
        $this->assertTrue($treeReader->hasNonNullValue('foo'));
        $this->assertFalse($treeReader->hasNonNullValue('baz'));
        $this->assertFalse($treeReader->hasNonNullValue('bat'));
    }

    public function scalarGetterProvider()
    {
        return [
            [
                'getString',
                'bar',
                'baz',
            ],
            [
                'getInt',
                5,
                6,
            ],
            [
                'getFloat',
                5.5,
                6.6,
            ],
            [
                'getBool',
                true,
                false,
            ],
        ];
    }

    public function childrenGetterProvider()
    {
        return [
            [
                'getChildren',
                ['baz' => 'bat'],
                ['bat' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider scalarGetterProvider
     * @dataProvider childrenGetterProvider
     */
    public function testNonExistentKeyExceptions(string $method)
    {
        $treeReader = new TreeReader([]);
        $this->expectException(KeyNotFoundException::class);
        $treeReader->{$method}('foo');
    }

    /**
     * @dataProvider scalarGetterProvider
     */
    public function testDefaultWithNonExistentKey(string $method, $value, $default)
    {
        $treeReader = new TreeReader([]);
        $this->assertSame($default, $treeReader->{$method}('foo', $default));
    }

    /**
     * @dataProvider scalarGetterProvider
     */
    public function testDefaultWithNullValue(string $method, $value, $default)
    {
        $treeReader = new TreeReader(['foo' => null]);
        $this->assertSame($default, $treeReader->{$method}('foo', $default));
    }

    /**
     * @dataProvider scalarGetterProvider
     * @dataProvider childrenGetterProvider
     */
    public function testInvalidTypeException(string $method)
    {
        $treeReader = new TreeReader(['foo' => new stdClass()]);
        $this->expectException(UnexpectedTypeException::class);
        $treeReader->{$method}('foo');
    }

    /**
     * @dataProvider scalarGetterProvider
     */
    public function testValidValue(string $method, $value, $default)
    {
        $treeReader = new TreeReader(['foo' => $value]);
        $this->assertSame($value, $treeReader->{$method}('foo', $default));
    }

    /**
     * @dataProvider childrenGetterProvider
     */
    public function testChildrenDefaultWithNonExistentKey(string $method, array $value, array $default)
    {
        $treeReader = new TreeReader([]);
        $this->assertAttributeSame($default, 'data', $treeReader->{$method}('foo', $default));
    }

    /**
     * @dataProvider childrenGetterProvider
     */
    public function testChildrenDefaultWithNullValue(string $method, array $value, array $default)
    {
        $treeReader = new TreeReader(['foo' => null]);
        $this->assertAttributeSame($default, 'data', $treeReader->{$method}('foo', $default));
    }

    /**
     * @dataProvider childrenGetterProvider
     */
    public function testChildrenValidValue(string $method, array $value, array $default)
    {
        $treeReader = new TreeReader(['foo' => $value]);
        $children = $treeReader->{$method}('foo', $default);
        $this->assertAttributeSame(['foo'], 'parentKeys', $children);
        $this->assertAttributeSame($value, 'data', $children);
    }

    public function testKeyNotFoundExceptionWithMultipleParents()
    {
        $treeReader = new TreeReader(['foo' => ['bar' => []]]);

        $this->expectException(KeyNotFoundException::class);
        $this->expectExceptionMessage('in tree "foo->bar"');
        $treeReader->getChildren('foo')->getChildren('bar')->getString('baz');
    }
}
