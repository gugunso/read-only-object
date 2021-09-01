<?php

namespace Gugunso\ReadOnlyObject;

/**
 * Class ReadOnlyObject
 * protected なプロパティに対して外部から参照を許容するオブジェクト。
 * @package Gugunso\ReadOnlyObject
 */
abstract class ReadOnlyObject extends BaseObject
{
    /**
     * retrievePropertyValuesAsArrayのエイリアス
     * 各実装クラスの中でアクセスしたい場合を想定して用意している。
     * @return array
     */
    protected function toArray(): array
    {
        return $this->retrievePropertyValuesAsArray();
    }
}
