<?php

use aldumas\Collections\Maps\OrderedMap\ArrayOrderedMap;
use aldumas\Collections\Maps\OrderedMap\ImmutableOrderedMap;
use aldumas\Collections\Exceptions\KeyNotFoundException;



class ImmutableOrderedMapTest extends PHPUnit_Framework_TestCase
{
    /** @var ArrayOrderedMap */
    private $map;

    /** @var ImmutableOrderedMap */
    private $immutable;

    public function setUp() {
        $this->map = new ArrayOrderedMap();
        $this->map->insert("Alpha", 42);
        $this->map->insert("Beta", 100);

        $this->immutable = new ImmutableOrderedMap($this->map);
    }

    public function testCount() {
        $this->assertEquals(2, count($this->immutable));
    }

    public function testHas() {
        $this->assertEquals(true, $this->immutable->has('Alpha'));
        $this->assertEquals(false, $this->immutable->has('Gamma'));
    }

    public function testGet() {
        $this->assertEquals(100, $this->immutable->get('Beta'));

        $this->setExpectedException(KeyNotFoundException::class);
        $this->immutable->get('Gamma');
    }

    public function testInPositionOrderForward() {
        $map = new ArrayOrderedMap();
        $map->insert("Gamma-1", 1);
        $map->insert("Alpha-2", 2);
        $map->insert("Beta-3", 3);

        $map = new ImmutableOrderedMap($map);

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

        $map = new ImmutableOrderedMap($map);

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

        $map = new ImmutableOrderedMap($map);

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

        $map = new ImmutableOrderedMap($map);

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

        $map = new ImmutableOrderedMap($map);

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

        $map = new ImmutableOrderedMap($map);

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
}
