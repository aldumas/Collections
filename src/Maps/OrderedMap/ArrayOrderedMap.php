<?php

namespace aldumas\Collections\Maps\OrderedMap;

use aldumas\Collections\Exceptions\DuplicateKeyException;
use aldumas\Collections\Exceptions\KeyNotFoundException;

/**
 * Array-based container for lists of objects which have a position and a key,
 * optimized for access by key, yet allows iteration in order of position or
 * key.
 *
 * {@inheritdoc}
 *
 * This implementation is serializable.
 *
 * @package aldumas\Containers
 */
class ArrayOrderedMap implements IOrderedMap {
    protected $valuesByKey = [];
    protected $valuesByPosition = [];

    private static $internalKeyPrefix = 'str:';
    private static $internalKeyPrefixLen = 4;


    public function insert($key, $value, $pos=null) {
        if (!(is_string($key) || is_int($key))) {
            throw new \InvalidArgumentException("\$key must be a string or an integer");
        }
        if (!(is_null($pos) || (is_int($pos) && $pos >= 0))) {
            throw new \InvalidArgumentException("\$pos must be a positive integer; was $pos");
        }
        if ($this->has($key)) {
            throw new DuplicateKeyException($key);
        }

        $position_value = $this->createPositionValueContainer($key, $value);

        $this->valuesByKey[static::getInternalKey($key)] = $value;
        if (is_null($pos)) {
            $this->valuesByPosition[] = $position_value;
        } else {
            array_splice($this->valuesByPosition, $pos, 0, [$position_value]);
        }
        return $this;
    }

    public function remove($key) {
        $value_pos = null;
        $value = $this->get($key);
        unset($this->valuesByKey[$this->getInternalKey($key)]);
        foreach ($this->valuesByPosition as $pos => $value_container) {
            if ($key === $this->getKeyFromPositionValueContainer($value_container)) {
                array_splice($this->valuesByPosition, $pos, 1);
                $value_pos = $pos;
                break;
            }
        }

        assert('!is_null($value_pos)', 'valuesByKey and valuesByPosition are out of sync');

        return [$value, $value_pos];
    }

    public function get($key) {
        if (!$this->has($key)) {
            throw new KeyNotFoundException($key);
        }
        return $this->valuesByKey[static::getInternalKey($key)];
    }

    public function has($key) {
        return array_key_exists(static::getInternalKey($key),
            $this->valuesByKey);
    }

    public function inPositionOrder($reverse=false) {
        if ($reverse) {
            for ($j = count($this->valuesByPosition) - 1; $j >= 0; --$j) {
                yield $j => $this->getValueFromPositionValueContainer(
                    $this->valuesByPosition[$j]);
            }
        }  else {
            for ($j = 0; $j < count($this->valuesByPosition); ++$j) {
                yield $j => $this->getValueFromPositionValueContainer(
                    $this->valuesByPosition[$j]);
            }
        }
    }

    public function inKeyOrder($reverse=false, $compare='strcmp') {
        $keys = static::withExternalKeys(array_keys($this->valuesByKey));
        usort($keys, $compare);
        if ($reverse) {
            $keys = array_reverse($keys);
        }

        for ($j = 0; $j < count($keys); ++$j) {
            $key = $keys[$j];
            yield $key => $this->valuesByKey[$this->getInternalKey($key)];
        }
    }

    protected static function getInternalKey($key) {
        if (is_string($key)) {
            return self::$internalKeyPrefix . $key;
        } else {
            return $key;
        }
    }

    protected static function getExternalKey($key) {
        if (is_string($key)) {
            return substr($key, self::$internalKeyPrefixLen);
        } else {
            return $key;
        }
    }

    protected function createPositionValueContainer($key, $value) {
            return compact('key', 'value');
    }

    protected function getValueFromPositionValueContainer($value_container) {
        return $value_container['value'];
    }

    protected function getKeyFromPositionValueContainer($value_container) {
        return $value_container['key'];
    }

    protected static function withExternalKeys(array $keys) {
        $external_keys = [];
        foreach ($keys as $key) {
            $external_keys[] = static::getExternalKey($key);
        }
        return $external_keys;
    }

    public function count() {
        return count($this->valuesByPosition);
    }
}
