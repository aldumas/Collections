<?php

namespace aldumas\Collections\Maps\OrderedMap;

/**
 * Interface for an immutable version of IOrderedMap.
 * @package aldumas\Collections\Maps\OrderedMap
 */
interface IImmutableOrderedMap extends \Countable {
    /**
     * Return the value assigned to the given key.
     *
     * @param $key string|int key assigned to value
     * @return mixed
     * @throw KeyNotFoundException if the key does not exist in the container
     */
    function get($key);

    /**
     * Return true if $key exists in the container.
     *
     * @param $key string|int key assigned to value
     * @return bool
     */
    function has($key);

    /**
     * Return iterator which iterates over all values in the container in order
     * of position.
     *
     * The iterator does not allow any modifications.
     *
     * @param $reverse bool true if iteration should occur highest to
     * lowest. Note that this only affects the order of iteration, not the
     * positions returned from the generator.
     * @return \Generator which returns position and value for each iteration
     */
    function inPositionOrder($reverse=false);

    /**
     * Return iterator which iterates over all values in the container in order
     * of key.
     *
     * The iterator does not allow any modifications.
     *
     * @param $reverse bool whether the iteration order should be reversed
     * @param $compare mixed a callable comparison function as would be passed
     * to usort.
     * @return \Generator which returns key and value for each iteration
     */
    function inKeyOrder($reverse=false, $compare='strcmp');

    /**
     * Return the number of values in the container.
     *
     * @return int
     */
    function count();
}