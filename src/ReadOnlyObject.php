<?php

namespace Gugunso\ReadOnlyObject;

use ArrayAccess;
use LogicException;

/**
 * Class ReadOnlyObject
 * @package Gugunso\ReadOnlyObject
 */
abstract class ReadOnlyObject implements ArrayAccess
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
        $allowedKeys = array_keys(get_class_vars(static::class));
        $asArray = [];
        foreach (get_object_vars($this) as $name => $value) {
            if (!in_array($name, $allowedKeys)) {
                continue;
            }
            if ('readOnlyObjectTmpArray' === $name) {
                continue;
            }
            if (is_object($value)) {
                $value = (string)$value;
            }
            $asArray[$name] = $value;
        }
        $this->readOnlyObjectTmpArray = $asArray;
        return $asArray;
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
}
