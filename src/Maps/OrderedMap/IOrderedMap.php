<?php

namespace aldumas\Collections\Maps\OrderedMap;

/**
 * Container for lists of objects which have a position and a key, optimized
 * for access by key, yet allows iteration in order of position or key.
 *
 * Duplicate keys are not allowed.
 *
 * @package aldumas\Containers
 */
interface IOrderedMap extends \Countable {
    /**
     * Insert a value into the container.
     *
     * @param $key string|int key assigned to value. string keys are
     * case-sensitive and numeric strings are considered different than
     * integer keys, e.g. '42' is a different key than 42.
     * @param $value mixed value
     * @param integer|null $pos, positive integer position or null to append
     * @return OrderedMap $this
     * @throw DuplicateKeyException if key already exists in the container
     * @throw InvalidArgumentException if $key is not a string or integer, or
     * if $pos is neither null nor a positive integer.
     */
    function insert($key, $value, $pos=null);

    /** Remove the value with the given key from the container.
     *
     * @param $key string|int key assigned to value.
     * @return array [value, position] of the key that was removed
     * @throw KeyNotFoundException if the key does not exist in the container
     */
    function remove($key);

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
     * @param $reverse bool whether the iteration order should be reversed
     * @param $compare mixed a callable comparison function as would be passed
     * to usort.
     * @return \Generator which returns key and value for each iteration
     */
    function inKeyOrder($reverse=false, $compare='strcmp');
}
