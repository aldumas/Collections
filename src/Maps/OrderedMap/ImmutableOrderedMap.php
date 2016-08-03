<?php

namespace aldumas\Collections\Maps\OrderedMap;

/**
 * Decorates an IOrderedMap so that it is not modifiable.
 *
 * @package aldumas\Collections\Maps\OrderedMap
 */
final class ImmutableOrderedMap implements IImmutableOrderedMap {
    private $map;

    function __construct(IOrderedMap $map) {
        $this->map = $map;
    }

    public function get($key) {
        return $this->map->get($key);
    }

    public function has($key) {
        return $this->map->has($key);
    }

    public function inPositionOrder($reverse=false) {
        return $this->map->inPositionOrder($reverse);
    }

    public function inKeyOrder($reverse=false, $compare='strcmp') {
        return $this->map->inKeyOrder($reverse, $compare);
    }

    public function count() {
        return count($this->map);
    }
}