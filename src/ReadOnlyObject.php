<?php

namespace Gugunso\ReadOnlyObject;

use ArrayAccess;
use LogicException;
use Traversable;

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
        $allowedKeys = array_keys(get_class_vars(static::class));
        foreach (get_object_vars($this) as $name => $value) {
            if (!in_array($name, $allowedKeys)) {
                continue;
            }
            if ('readOnlyObjectTmpArray' === $name) {
                continue;
            }
            yield $name => $value;
        }
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

}
