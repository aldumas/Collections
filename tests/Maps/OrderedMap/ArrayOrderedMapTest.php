<?php

use aldumas\Collections\Maps\OrderedMap\ArrayOrderedMap;
use aldumas\Collections\Exceptions\DuplicateKeyException;
use aldumas\Collections\Exceptions\KeyNotFoundException;



class ArrayOrderedMapTest extends PHPUnit_Framework_TestCase
{
    public function insertTypesProvider() {
        return [
            ['string key', 42, 'string/int'],
            [42, 'string value', 'int/string'],
            ['42', 'string value', 'numeric string/string'],
            ['string key', '42', 'string/numeric string'],
            ['string key', null, 'string/null'],
            ['string key', $this, 'string/object'],
            ['string key', [1, 2, 3], 'string/array'],
            ['', 0, 'empty string/int']
        ];
    }

    /**
     * @dataProvider  insertTypesProvider
     * @param $key int|string
     * @param $value mixed
     * @param $message string failure description
     */
    public function testInsertTypes($key, $value, $message) {
        $map = new ArrayOrderedMap();
        $map->insert($key, $value);
        $this->assertTrue($map->has($key), $message);
        $actual_value = $map->get($key);
        $this->assertSame($value, $actual_value, $message);
    }

    public function testCountEmpty() {
        $map = new ArrayOrderedMap();
        $this->assertEquals(0, count($map));
    }

    public function testCountNotEmpty() {
        $map = new ArrayOrderedMap();
        $map->insert("Alpha", 42);
        $map->insert("Beta", 100);
        $this->assertEquals(2, count($map));
    }

    public function testCountNotEmptyInsertByPos() {
        $map = new ArrayOrderedMap();
        $map->insert("Alpha", 42, 15);
        $this->assertEquals(1, count($map));
    }

    public function testInsertDuplicateKey() {
        $map = new ArrayOrderedMap();
        $map->insert("Alpha", 42);

        $this->setExpectedException(DuplicateKeyException::class);
        $map->insert("Alpha", 100);
    }

    public function testInsertInvalidPos() {
        $map = new ArrayOrderedMap();
        $this->setExpectedException(InvalidArgumentException::class);
        $map->insert("Alpha", 42, -1);
    }

    public function testInsertLargePos() {
        $map = new ArrayOrderedMap();
        $map->insert("Alpha", 42, 500);
        $this->assertEquals(1, count($map));
    }

    public function testInsertLargePosThenSmallPosOrder() {
        $map = new ArrayOrderedMap();
        $map->insert("Alpha", 42, 500); //re-indexed to count($map)
        $map->insert("Beta", 5432, 100);  //re-indexed to count($map)

        //expect Beta to cover AFTER Alpha even though $pos was less since the
        //container re-indexes.

        $values = iterator_to_array($map->inPositionOrder());
        $this->assertEquals(42, $values[0]);
        $this->assertEquals(5432, $values[1]);
    }

    public function testKeyNotFound() {
        $map = new ArrayOrderedMap();

        $this->setExpectedException(KeyNotFoundException::class);
        $map->get('DoesNotExist');
    }

    public function testKeyCaseMatters() {
        $map = new ArrayOrderedMap();
        $map->insert("ALPHA", 42);

        $this->setExpectedException(KeyNotFoundException::class);
        $map->get('alpha');
    }

    public function testKeyTypeMatters() {
        $map = new ArrayOrderedMap();
        $map->insert('42', 100);

        $this->setExpectedException(KeyNotFoundException::class);
        $map->get(42);
    }

    public function testInPositionOrderForward() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-1", 1);
        $map->insert("Alpha-2", 2);
        $map->insert("Beta-3", 3);

        $expectedOrder = [
            ['pos'=>0, 'val'=>1],
            ['pos'=>1, 'val'=>2],
            ['pos'=>2, 'val'=>3]
        ];
        $j = 0;
        foreach ($map->inPositionOrder() as $pos => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['pos'], $pos);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testInPositionOrderReverse() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-1", 1);
        $map->insert("Alpha-2", 2);
        $map->insert("Beta-3", 3);

        //the $reverse flag controls the order of iteration. The actual
        //position values are still returned.
        $expectedOrder = [
            ['pos'=>2, 'val'=>3],
            ['pos'=>1, 'val'=>2],
            ['pos'=>0, 'val'=>1]
        ];
        $j = 0;
        foreach ($map->inPositionOrder(true) as $pos => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['pos'], $pos);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testInKeyOrderDefault() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-1", 1);
        $map->insert("Alpha-2", 2);
        $map->insert("Beta-3", 3);

        $expectedOrder = [
            ['key'=>'Alpha-2', 'val'=>2],
            ['key'=>'Beta-3', 'val'=>3],
            ['key'=>'Gamma-1', 'val'=>1]
        ];
        $j = 0;
        foreach ($map->inKeyOrder() as $key => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['key'], $key);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testInKeyOrderReverse() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-1", 1);
        $map->insert("Alpha-2", 2);
        $map->insert("Beta-3", 3);

        $expectedOrder = [
            ['key'=>'Gamma-1', 'val'=>1],
            ['key'=>'Beta-3', 'val'=>3],
            ['key'=>'Alpha-2', 'val'=>2],
        ];
        $j = 0;
        foreach ($map->inKeyOrder(true) as $key => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['key'], $key);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testInKeyOrderCustomCompare() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-10", 10);
        $map->insert("Gamma-1", 1);
        $map->insert("Gamma-100", 100);

        $prefix_length = strlen('Gamma-');
        $compare = function($a, $b) use ($prefix_length) {
            $a_num = intval(substr($a, $prefix_length));
            $b_num = intval(substr($b, $prefix_length));
            return $a_num - $b_num;
        };

        $expectedOrder = [
            ['key'=>'Gamma-1', 'val'=>1],
            ['key'=>'Gamma-10', 'val'=>10],
            ['key'=>'Gamma-100', 'val'=>100],
        ];
        $j = 0;
        foreach ($map->inKeyOrder(false, $compare) as $key => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['key'], $key);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testInKeyOrderCustomCompareReverse() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-10", 10);
        $map->insert("Gamma-1", 1);
        $map->insert("Gamma-100", 100);

        $prefix_length = strlen('Gamma-');
        $compare = function($a, $b) use ($prefix_length) {
            $a_num = intval(substr($a, $prefix_length));
            $b_num = intval(substr($b, $prefix_length));
            return $a_num - $b_num;
        };

        $expectedOrder = [
            ['key'=>'Gamma-100', 'val'=>100],
            ['key'=>'Gamma-10', 'val'=>10],
            ['key'=>'Gamma-1', 'val'=>1],
        ];
        $j = 0;
        foreach ($map->inKeyOrder(true, $compare) as $key => $value) {
            $expected = $expectedOrder[$j];
            $this->assertEquals($expected['key'], $key);
            $this->assertEquals($expected['val'], $value);
            ++$j;
        }
    }

    public function testRemove() {
        $map = new ArrayOrderedMap();
        $map->insert('Alpha', 42);
        $value_to_remove = new \stdClass();
        $map->insert('Beta', $value_to_remove);
        $map->insert('Gamma', 200);

        list($value, $pos) = $map->remove('Beta');
        $this->assertSame($value_to_remove, $value);
        $this->assertEquals(1, $pos);

        $this->setExpectedException(KeyNotFoundException::class);
        $map->remove('DoesntExist');

    }
}
