<?php

namespace Gugunso\ReadOnlyObject;

use ArrayAccess;
use LogicException;

/**
 * Class ReadOnlyArray
 * 配列のように扱えるReadOnlyなオブジェクト
 * publicなプロパティを定義した場合、そのプロパティに対して値がsetできてしまうため注意。
 * @package Gugunso\ReadOnlyObject
 */
abstract class ReadOnlyArray extends BaseObject implements ArrayAccess
{
    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toArray());
    }

    /**
     * retrievePropertyValuesAsArrayのエイリアス。
     * このオブジェクトは、配列同様に扱えることを意図しているため、外部からも呼び出し可能としている。
     * @return array
     */
    public function toArray(): array
    {
        return $this->retrievePropertyValuesAsArray();
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
