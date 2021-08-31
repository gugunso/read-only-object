<?php

namespace Gugunso\ReadOnlyObject;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * Class ReadOnlyArray
 * 配列のように扱えるReadOnlyなオブジェクト
 * publicなプロパティを定義した場合、そのプロパティに対して値がsetできてしまうため注意。
 * @package Gugunso\ReadOnlyObject
 */
abstract class ReadOnlyArray implements ArrayAccess, IteratorAggregate
{
    /** @var array|null */
    private $readOnlyObjectTmpArray;

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (!is_null($this->readOnlyObjectTmpArray)) {
            return $this->readOnlyObjectTmpArray;
        }
        $asArray = [];
        foreach ($this->getVars() as $name => $value) {
            $asArray[$name] = $this->castValue($value);
        }
        $this->readOnlyObjectTmpArray = $asArray;
        return $asArray;
    }

    /**
     * @return Traversable
     */
    private function getVars(): Traversable
    {
        $objectVars = get_object_vars($this);
        foreach ($this->getAllowedKeys() as $name) {
            yield $name => $objectVars[$name];
        }
    }

    /**
     * @return array
     */
    private function getAllowedKeys()
    {
        $allowedKeys = array_keys(get_class_vars(static::class));
        if ($index = array_search('readOnlyObjectTmpArray', $allowedKeys)) {
            unset($allowedKeys[$index]);
        }
        return $allowedKeys;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function castValue($value)
    {
        if (is_object($value)) {
            return (string)$value;
        }
        return $value;
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $array = $this->toArray();
        return $array[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException(
            'You have tried to set a value for ' . $offset . '. ' . static::class . ' is read only.'
        );
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('You have tried to unset ' . $offset . '. ' . static::class . ' is read only.');
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    final public function __set($name, $value)
    {
        throw new LogicException(
            'You have tried to set a value for ' . $name . '. ' . static::class . ' is read only.'
        );
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }
}
